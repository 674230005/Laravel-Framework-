<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\RepairRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder สำหรับสร้างข้อมูลเริ่มต้นของระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า
 * สร้างผู้ใช้งานครบ 3 บทบาท (Admin, Technician, User)
 * พร้อมข้อมูลเครื่องใช้ไฟฟ้าและรายการแจ้งซ่อมตัวอย่าง
 */
class DatabaseSeeder extends Seeder
{
    /**
     * รันการสร้างข้อมูลเริ่มต้น
     */
    public function run(): void
    {
        // 1. สร้างบัญชีผู้ดูแลระบบ (Admin)
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'ผู้ดูแลระบบ (Admin)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081-111-2222',
                'department' => 'ฝ่ายบริหารอาคารและสถานที่',
            ]
        );
        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'phone' => '081-111-2222',
                'department' => 'ฝ่ายบริหารอาคารและสถานที่',
                'position' => 'หัวหน้าฝ่ายดูแลระบบ',
            ]
        );

        // 2. สร้างบัญชีช่างซ่อมคนที่ 1 (Technician)
        $tech1 = User::firstOrCreate(
            ['email' => 'tech1@company.com'],
            [
                'name' => 'สมชาย ช่างไฟ (Technician)',
                'password' => Hash::make('password123'),
                'role' => 'technician',
                'phone' => '082-222-3333',
                'department' => 'ฝ่ายซ่อมบำรุง',
            ]
        );
        UserProfile::firstOrCreate(
            ['user_id' => $tech1->id],
            [
                'phone' => '082-222-3333',
                'department' => 'ฝ่ายซ่อมบำรุง',
                'position' => 'ช่างเทคนิคระบบไฟฟ้า',
            ]
        );

        // 3. สร้างบัญชีช่างซ่อมคนที่ 2 (Technician)
        $tech2 = User::firstOrCreate(
            ['email' => 'tech2@company.com'],
            [
                'name' => 'วิชัย ช่างแอร์ (Technician)',
                'password' => Hash::make('password123'),
                'role' => 'technician',
                'phone' => '083-333-4444',
                'department' => 'ฝ่ายซ่อมบำรุง',
            ]
        );
        UserProfile::firstOrCreate(
            ['user_id' => $tech2->id],
            [
                'phone' => '083-333-4444',
                'department' => 'ฝ่ายซ่อมบำรุง',
                'position' => 'ช่างระบบทำความเย็น',
            ]
        );

        // 4. สร้างบัญชีผู้ใช้งานทั่วไปคนที่ 1 (User / Staff)
        $user1 = User::firstOrCreate(
            ['email' => 'user1@company.com'],
            [
                'name' => 'สมศรี มีทรัพย์ (User)',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'phone' => '084-444-5555',
                'department' => 'ฝ่ายการเงินและบัญชี',
            ]
        );
        UserProfile::firstOrCreate(
            ['user_id' => $user1->id],
            [
                'phone' => '084-444-5555',
                'department' => 'ฝ่ายการเงินและบัญชี',
                'position' => 'เจ้าหน้าที่บัญชีอาวุโส',
            ]
        );

        // 5. สร้างบัญชีผู้ใช้งานทั่วไปคนที่ 2 (User / Staff)
        $user2 = User::firstOrCreate(
            ['email' => 'user2@company.com'],
            [
                'name' => 'กิตติศักดิ์ ฝ่ายขาย (User)',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'phone' => '085-555-6666',
                'department' => 'ฝ่ายขายและการตลาด',
            ]
        );
        UserProfile::firstOrCreate(
            ['user_id' => $user2->id],
            [
                'phone' => '085-555-6666',
                'department' => 'ฝ่ายขายและการตลาด',
                'position' => 'เจ้าหน้าที่การตลาด',
            ]
        );

        // 6. สร้างข้อมูลเครื่องใช้ไฟฟ้าเริ่มต้น (Equipment)
        $eq1 = Equipment::firstOrCreate(
            ['code' => 'EQ-AC-001'],
            [
                'name' => 'เครื่องปรับอากาศ Daikin Inverter',
                'type' => 'เครื่องปรับอากาศ',
                'location' => 'ห้องสำนักงาน ชั้น 2',
                'status' => 'ชำรุด',
                'description' => 'เครื่องปรับอากาศขนาด 18,000 BTU ใช้งานมา 2 ปี',
            ]
        );

        $eq2 = Equipment::firstOrCreate(
            ['code' => 'EQ-PR-002'],
            [
                'name' => 'เครื่องพิมพ์ HP LaserJet Pro',
                'type' => 'เครื่องพิมพ์',
                'location' => 'ห้องธุรการ ชั้น 1',
                'status' => 'กำลังซ่อม',
                'description' => 'เครื่องพิมพ์เลเซอร์ขาวดำสำหรับงานเอกสาร',
            ]
        );

        $eq3 = Equipment::firstOrCreate(
            ['code' => 'EQ-PC-003'],
            [
                'name' => 'คอมพิวเตอร์ตั้งโต๊ะ Dell OptiPlex',
                'type' => 'คอมพิวเตอร์',
                'location' => 'ห้อง IT ชั้น 3',
                'status' => 'ปกติ',
                'description' => 'เครื่องคอมพิวเตอร์ตั้งโต๊ะ ประจำห้อง IT',
            ]
        );

        $eq4 = Equipment::firstOrCreate(
            ['code' => 'EQ-RF-004'],
            [
                'name' => 'ตู้เย็น Mitsubishi 2 ประตู',
                'type' => 'ตู้เย็น',
                'location' => 'ห้องแพนทรี่ ชั้น 2',
                'status' => 'ปกติ',
                'description' => 'ตู้เย็นส่วนกลางสำหรับเก็บอาหารและเครื่องดื่มพนักงาน',
            ]
        );

        $eq5 = Equipment::firstOrCreate(
            ['code' => 'EQ-FN-005'],
            [
                'name' => 'พัดลมตั้งพื้น Hatari 16 นิ้ว',
                'type' => 'พัดลม',
                'location' => 'ห้องประชุมเล็ก 2',
                'status' => 'ปกติ',
                'description' => 'พัดลมเสริมในห้องประชุม',
            ]
        );

        // 7. สร้างรายการแจ้งซ่อมตัวอย่าง (ตรงตามที่ปรากฏใน UI Mock เดิม RE-001, RE-002, RE-003)
        // รายการที่ 1: สถานะ รอดำเนินการ
        RepairRequest::firstOrCreate(
            ['tracking_number' => 'RE-001'],
            [
                'user_id' => $user1->id,
                'technician_id' => null,
                'equipment_id' => $eq1->id,
                'reporter_name' => 'สมศรี มีทรัพย์',
                'equipment_name' => 'เครื่องปรับอากาศ',
                'equipment_type' => 'เครื่องปรับอากาศ',
                'location' => 'ห้องสำนักงาน ชั้น 2',
                'priority' => 'สูง',
                'description' => 'เครื่องปรับอากาศเปิดไม่ติด มีไฟกระพริบสีแดงและมีเสียงดังผิดปกติ',
                'status' => 'รอดำเนินการ',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        // รายการที่ 2: สถานะ กำลังซ่อม (มอบหมายให้ช่างสมชายแล้ว)
        RepairRequest::firstOrCreate(
            ['tracking_number' => 'RE-002'],
            [
                'user_id' => $user1->id,
                'technician_id' => $tech1->id,
                'equipment_id' => $eq2->id,
                'reporter_name' => 'สมศรี มีทรัพย์',
                'equipment_name' => 'เครื่องพิมพ์',
                'equipment_type' => 'เครื่องพิมพ์',
                'location' => 'ห้องธุรการ ชั้น 1',
                'priority' => 'ปานกลาง',
                'description' => 'กระดาษติดบ่อย และไฟสถานะเตือนข้อผิดพลาดขึ้นสีส้ม',
                'status' => 'กำลังซ่อม',
                'admin_notes' => 'มอบหมายให้ช่างสมชายตรวจสอบชุดลูกกลิ้งดึงกระดาษ',
                'inspection_result' => 'ตรวจพบชุดลูกกลิ้งดึงกระดาษสึกหรอและมีเศษกระดาษติดด้านใน',
                'parts_used' => 'ลูกกลิ้งดึงกระดาษ (Roller) 1 ชุด',
                'repair_detail' => 'กำลังถอดเปลี่ยนชุดลูกกลิ้งและทำความสะอาดรางวิ่งกระดาษ',
                'assigned_at' => now()->subDay(),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subHours(5),
            ]
        );

        // รายการที่ 3: สถานะ ซ่อมเสร็จ
        RepairRequest::firstOrCreate(
            ['tracking_number' => 'RE-003'],
            [
                'user_id' => $user2->id,
                'technician_id' => $tech1->id,
                'equipment_id' => $eq3->id,
                'reporter_name' => 'กิตติศักดิ์ ฝ่ายขาย',
                'equipment_name' => 'คอมพิวเตอร์',
                'equipment_type' => 'คอมพิวเตอร์',
                'location' => 'ห้อง IT ชั้น 3',
                'priority' => 'ต่ำ',
                'description' => 'พาวเวอร์ซัพพลายมีกลิ่นไหม้ เปิดเครื่องไม่ติด ไฟเมนบอร์ดไม่ขึ้น',
                'status' => 'ซ่อมเสร็จ',
                'admin_notes' => 'อนุมัติการเปลี่ยนอะไหล่พาวเวอร์ซัพพลาย',
                'inspection_result' => 'Power Supply เสียหายเนื่องจากไฟกระชาก',
                'parts_used' => 'Power Supply 550W 80 Plus 1 ตัว',
                'repair_detail' => 'เปลี่ยนพาวเวอร์ซัพพลายตัวใหม่ ทดสอบรันระบบ 2 ชั่วโมง เครื่องเปิดติดทำงานได้สมบูรณ์ ส่งมอบคืนผู้ใช้งานเรียบร้อยแล้ว',
                'assigned_at' => now()->subDays(4),
                'completed_at' => now()->subDays(1),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ]
        );
    }
}
