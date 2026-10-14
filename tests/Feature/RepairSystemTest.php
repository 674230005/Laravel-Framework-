<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature Test: ทดสอบระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าครบวงจร
 * ครอบคลุม:
 * 1. การเข้าสู่ระบบและการออกจากระบบ
 * 2. การควบคุมสิทธิ์ตามบทบาท (Role-based access: Admin, Technician, User)
 * 3. กระบวนการแจ้งซ่อมของพนักงาน (สร้างงาน, อัปโหลดรูปภาพ, ออกเลขที่งาน)
 * 4. แอดมินมอบหมายงานให้ช่าง
 * 5. ช่างอัปเดตสถานะ บันทึกอาการ อะไหล่ และปิดงานซ่อม
 * 6. แอดมินดูรายงานตามสถานะ
 */
class RepairSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ข้อมูลผู้ใช้สำหรับทดสอบ
     */
    protected User $admin;
    protected User $technician;
    protected User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // สร้างผู้ใช้ 3 บทบาท
        $this->admin = User::create([
            'name' => 'แอดมินทดสอบ',
            'email' => 'admin_test@company.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '0811111111',
            'department' => 'ส่วนกลาง',
        ]);

        $this->technician = User::create([
            'name' => 'ช่างทดสอบ',
            'email' => 'tech_test@company.com',
            'password' => Hash::make('password123'),
            'role' => 'technician',
            'phone' => '0822222222',
            'department' => 'ฝ่ายซ่อม',
        ]);

        $this->normalUser = User::create([
            'name' => 'ผู้ใช้ทดสอบ',
            'email' => 'user_test@company.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone' => '0833333333',
            'department' => 'ฝ่ายขาย',
        ]);
    }

    /**
     * ทดสอบ: การล็อกอินด้วยรหัสผ่านที่ถูกต้อง
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => $this->normalUser->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->normalUser);
    }

    /**
     * ทดสอบ: ล็อกอินไม่ผ่านหากรหัสผ่านผิด
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $response = $this->post('/login', [
            'email' => $this->normalUser->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * ทดสอบ: ผู้ใช้สามารถออกจากระบบได้
     */
    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->normalUser)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ทดสอบ: ผู้ใช้ทั่วไปไม่สามารถเข้าถึงหน้าของผู้ดูแลระบบได้ (ถูก Redirect กลับ)
     */
    public function test_normal_user_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->normalUser)->get('/admin/repairs');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');
    }

    /**
     * ทดสอบ: ผู้ใช้ทั่วไปไม่สามารถเข้าถึงหน้างานของช่างซ่อมได้
     */
    public function test_normal_user_cannot_access_technician_routes(): void
    {
        $response = $this->actingAs($this->normalUser)->get('/technician/repairs');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');
    }

    /**
     * ทดสอบ: แอดมินสามารถเข้าถึงหน้ารายการงานซ่อมของผู้ดูแลระบบได้
     */
    public function test_admin_can_access_admin_repairs(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/repairs');

        $response->assertStatus(200);
        $response->assertSee('จัดการงานแจ้งซ่อมทั้งหมด');
    }

    /**
     * ทดสอบ: ช่างซ่อมสามารถเข้าถึงหน้างานที่ตนเองรับผิดชอบได้
     */
    public function test_technician_can_access_technician_repairs(): void
    {
        $response = $this->actingAs($this->technician)->get('/technician/repairs');

        $response->assertStatus(200);
        $response->assertSee('งานซ่อมที่ฉันรับผิดชอบ');
    }

    /**
     * ทดสอบกระบวนการแจ้งซ่อม: ผู้ใช้ส่งฟอร์มแจ้งซ่อมพร้อมแนบรูปภาพ
     */
    public function test_user_can_submit_repair_request_with_image(): void
    {
        Storage::fake('public');

        // ใช้ create() กำหนด MIME Type เป็น image/jpeg โดยไม่ต้องพึ่งพาฟังก์ชัน GD ของ PHP
        $fakeImage = UploadedFile::fake()->create('broken_ac.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->normalUser)->post('/repairs', [
            'name' => 'ผู้ใช้ทดสอบ',
            'equipment' => 'เครื่องปรับอากาศ Panasonic ห้อง 301',
            'type' => 'เครื่องปรับอากาศ',
            'location' => 'อาคาร 3 ชั้น 3 ห้อง 301',
            'priority' => 'สูง',
            'description' => 'แอร์ไม่เย็น มีน้ำหยดลงบนโต๊ะทำงาน',
            'image' => $fakeImage,
        ]);

        // ตรวจสอบว่าบันทึกลงฐานข้อมูลเรียบร้อย
        $this->assertDatabaseHas('repair_requests', [
            'user_id' => $this->normalUser->id,
            'equipment_name' => 'เครื่องปรับอากาศ Panasonic ห้อง 301',
            'status' => 'รอดำเนินการ',
            'priority' => 'สูง',
        ]);

        $repair = RepairRequest::where('equipment_name', 'เครื่องปรับอากาศ Panasonic ห้อง 301')->first();
        $this->assertNotNull($repair);
        $this->assertNotNull($repair->image_path);
        $this->assertStringStartsWith('RE-', $repair->tracking_number);

        // ตรวจสอบว่าไฟล์ถูกจัดเก็บในดิสก์ public
        Storage::disk('public')->assertExists($repair->image_path);

        $response->assertRedirect(route('repairs.show', $repair));
    }

    /**
     * ทดสอบ: แอดมินสามารถมอบหมายงานให้ช่างและเปลี่ยนสถานะได้
     */
    public function test_admin_can_assign_technician_and_update_status(): void
    {
        $repair = RepairRequest::create([
            'tracking_number' => 'RE-999',
            'user_id' => $this->normalUser->id,
            'reporter_name' => 'ผู้ใช้ทดสอบ',
            'equipment_name' => 'พัดลมดูดอากาศ',
            'equipment_type' => 'พัดลม',
            'location' => 'ห้องน้ำชั้น 1',
            'priority' => 'ปานกลาง',
            'description' => 'พัดลมไม่หมุน',
            'status' => 'รอดำเนินการ',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/repairs/{$repair->id}/assign", [
            'technician_id' => $this->technician->id,
            'status' => 'รับเรื่องแล้ว',
            'admin_notes' => 'มอบหมายให้ช่างทดสอบตรวจเช็กมอเตอร์',
        ]);

        $response->assertSessionHas('success');

        $repair->refresh();
        $this->assertEquals($this->technician->id, $repair->technician_id);
        $this->assertEquals('รับเรื่องแล้ว', $repair->status);
        $this->assertNotNull($repair->assigned_at);
        $this->assertEquals('มอบหมายให้ช่างทดสอบตรวจเช็กมอเตอร์', $repair->admin_notes);
    }

    /**
     * ทดสอบ: ช่างสามารถอัปเดตอาการ อะไหล่ ผลการซ่อม และปิดงานซ่อมได้
     */
    public function test_technician_can_update_repair_details_and_complete_job(): void
    {
        $repair = RepairRequest::create([
            'tracking_number' => 'RE-998',
            'user_id' => $this->normalUser->id,
            'technician_id' => $this->technician->id,
            'reporter_name' => 'ผู้ใช้ทดสอบ',
            'equipment_name' => 'เครื่องพิมพ์ใบเสร็จ',
            'equipment_type' => 'เครื่องพิมพ์',
            'location' => 'แคชเชียร์ 1',
            'priority' => 'เร่งด่วน',
            'description' => 'เครื่องพิมพ์ไม่ตอบสนอง',
            'status' => 'กำลังซ่อม',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->technician)->post("/technician/repairs/{$repair->id}", [
            'status' => 'ซ่อมเสร็จ',
            'inspection_result' => 'ตรวจพบสายพานขับหัวพิมพ์ขาด',
            'parts_used' => 'สายพานขับหัวพิมพ์ 1 เส้น',
            'repair_detail' => 'เปลี่ยนสายพานและทดสอบพิมพ์ข้อความ 10 หน้า ทำงานได้ราบรื่น',
        ]);

        $response->assertSessionHas('success');

        $repair->refresh();
        $this->assertEquals('ซ่อมเสร็จ', $repair->status);
        $this->assertEquals('ตรวจพบสายพานขับหัวพิมพ์ขาด', $repair->inspection_result);
        $this->assertEquals('สายพานขับหัวพิมพ์ 1 เส้น', $repair->parts_used);
        $this->assertNotNull($repair->completed_at);
    }

    /**
     * ทดสอบ: แอดมินสามารถเปิดดูหน้ารายงานตามสถานะและช่วงเวลาได้
     */
    public function test_admin_can_view_reports(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports?status=ซ่อมเสร็จ');

        $response->assertStatus(200);
        $response->assertSee('รายงานสรุปงานซ่อม');
    }
}
