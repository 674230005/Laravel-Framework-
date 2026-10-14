<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration สำหรับปรับปรุงโครงสร้างตารางในระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า
 * - เพิ่มข้อมูลบทบาท (Role) และข้อมูลติดต่อในตาราง users
 * - เพิ่มคอลัมน์ข้อมูลอุปกรณ์ในตาราง equipment
 * - เพิ่มคอลัมน์ข้อมูลผู้ใช้เพิ่มเติมในตาราง user_profiles
 * - เพิ่มคอลัมน์รายการแจ้งซ่อม การมอบหมายช่าง และผลการซ่อมในตาราง repair_requests
 */
return new class extends Migration
{
    /**
     * ดำเนินการเพิ่มคอลัมน์ลงในฐานข้อมูล
     */
    public function up(): void
    {
        // 1. เพิ่มข้อมูลบทบาทและแผนกในตาราง users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                // บทบาท: user = ผู้ใช้ทั่วไป, technician = ช่างซ่อม, admin = ผู้ดูแลระบบ
                $table->string('role')->default('user')->after('password');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                // เบอร์โทรศัพท์ติดต่อ
                $table->string('phone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'department')) {
                // แผนกหรือฝ่ายที่สังกัด
                $table->string('department')->nullable()->after('phone');
            }
        });

        // 2. ปรับปรุงตาราง equipment (ข้อมูลเครื่องใช้ไฟฟ้าในระบบ)
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'code')) {
                // รหัสครุภัณฑ์หรือรหัสอุปกรณ์ เช่น EQ-AC-001
                $table->string('code')->nullable()->unique();
            }
            if (!Schema::hasColumn('equipment', 'name')) {
                // ชื่ออุปกรณ์ เช่น แอร์ Daikin ห้องประชุม 1
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'type')) {
                // ประเภทอุปกรณ์ เช่น เครื่องปรับอากาศ, คอมพิวเตอร์
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'location')) {
                // สถานที่ตั้งของอุปกรณ์ เช่น อาคาร A ชั้น 2
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'status')) {
                // สถานะอุปกรณ์: ปกติ, ชำรุด, กำลังซ่อม, ปลดระวาง
                $table->string('status')->default('ปกติ');
            }
            if (!Schema::hasColumn('equipment', 'description')) {
                // รายละเอียดเพิ่มเติมหรือสเปกของอุปกรณ์
                $table->text('description')->nullable();
            }
        });

        // 3. ปรับปรุงตาราง user_profiles (ข้อมูลประวัติเพิ่มเติมของผู้ใช้)
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'user_id')) {
                // เชื่อมโยงกับตาราง users
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('user_profiles', 'phone')) {
                // เบอร์โทรศัพท์
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('user_profiles', 'department')) {
                // แผนก
                $table->string('department')->nullable();
            }
            if (!Schema::hasColumn('user_profiles', 'position')) {
                // ตำแหน่งงาน
                $table->string('position')->nullable();
            }
        });

        // 4. ปรับปรุงตาราง repair_requests (ตารางหลักสำหรับรายการแจ้งซ่อม)
        Schema::table('repair_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_requests', 'tracking_number')) {
                // เลขที่ใบแจ้งซ่อม เช่น RE-001 หรือ RE-202610-001
                $table->string('tracking_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('repair_requests', 'user_id')) {
                // ผู้แจ้งซ่อม (เชื่อมโยงกับตาราง users)
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('repair_requests', 'technician_id')) {
                // ช่างผู้รับผิดชอบงานซ่อม (เชื่อมโยงกับตาราง users)
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('repair_requests', 'equipment_id')) {
                // อุปกรณ์ที่แจ้งซ่อม (ถ้าเชื่อมโยงกับตาราง equipment)
                $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            }
            if (!Schema::hasColumn('repair_requests', 'reporter_name')) {
                // ชื่อผู้แจ้งซ่อม (กรอกในฟอร์มหรือดึงจากโปรไฟล์)
                $table->string('reporter_name')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'equipment_name')) {
                // ชื่อเครื่องใช้ไฟฟ้า/อุปกรณ์ที่แจ้งซ่อม
                $table->string('equipment_name')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'equipment_type')) {
                // ประเภทอุปกรณ์ เช่น เครื่องปรับอากาศ, คอมพิวเตอร์
                $table->string('equipment_type')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'location')) {
                // สถานที่ตั้งของอุปกรณ์ เช่น ชั้น 2 ห้องสำนักงาน
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'priority')) {
                // ระดับความเร่งด่วน: ต่ำ, ปานกลาง, สูง, เร่งด่วน
                $table->string('priority')->default('ปานกลาง');
            }
            if (!Schema::hasColumn('repair_requests', 'description')) {
                // อาการเสีย / รายละเอียดปัญหาที่พบ
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'image_path')) {
                // รูปภาพอุปกรณ์ที่เสีย (ที่อยู่ไฟล์ใน storage)
                $table->string('image_path')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'status')) {
                // สถานะ: รอดำเนินการ, รับเรื่องแล้ว, กำลังตรวจสอบ, กำลังซ่อม, ซ่อมเสร็จ, ยกเลิก
                $table->string('status')->default('รอดำเนินการ');
            }
            if (!Schema::hasColumn('repair_requests', 'admin_notes')) {
                // หมายเหตุจากแอดมินหรือคำสั่งการ
                $table->text('admin_notes')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'inspection_result')) {
                // ผลการตรวจสอบของช่าง / อาการที่ตรวจพบจริง
                $table->text('inspection_result')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'parts_used')) {
                // อะไหล่ที่ใช้ในการซ่อม
                $table->text('parts_used')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'repair_detail')) {
                // รายละเอียดผลการซ่อม / วิธีการแก้ไข
                $table->text('repair_detail')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'assigned_at')) {
                // วันเวลาที่มอบหมายงานให้ช่าง
                $table->timestamp('assigned_at')->nullable();
            }
            if (!Schema::hasColumn('repair_requests', 'completed_at')) {
                // วันเวลาที่ดำเนินการซ่อมเสร็จสิ้น
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    /**
     * ยกเลิกการเพิ่มคอลัมน์ (ย้อนกลับ)
     */
    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['technician_id']);
            $table->dropForeign(['equipment_id']);
            $table->dropColumn([
                'tracking_number', 'user_id', 'technician_id', 'equipment_id',
                'reporter_name', 'equipment_name', 'equipment_type', 'location',
                'priority', 'description', 'image_path', 'status',
                'admin_notes', 'inspection_result', 'parts_used', 'repair_detail',
                'assigned_at', 'completed_at'
            ]);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'phone', 'department', 'position']);
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['code', 'name', 'type', 'location', 'status', 'description']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'department']);
        });
    }
};
