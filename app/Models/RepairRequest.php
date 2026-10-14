<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model RepairRequest: จัดการข้อมูลคำขอแจ้งซ่อมเครื่องใช้ไฟฟ้า
 * ควบคุมสถานะและขั้นตอนการซ่อม:
 * 1. รอดำเนินการ (ผู้ใช้ส่งฟอร์ม)
 * 2. รับเรื่องแล้ว (แอดมินตรวจสอบ/มอบหมายงาน)
 * 3. กำลังตรวจสอบ (ช่างลงพื้นที่ตรวจสอบอาการ)
 * 4. กำลังซ่อม (ช่างดำเนินการแก้ไข/เปลี่ยนอะไหล่)
 * 5. ซ่อมเสร็จ (ช่างบันทึกผลและปิดงาน)
 * 6. ยกเลิก (กรณีข้อมูลไม่ถูกต้องหรือยกเลิกคำขอ)
 */
class RepairRequest extends Model
{
    use HasFactory;

    /**
     * ชื่อตารางในฐานข้อมูล
     */
    protected $table = 'repair_requests';

    /**
     * ฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'tracking_number',
        'user_id',
        'technician_id',
        'equipment_id',
        'reporter_name',
        'equipment_name',
        'equipment_type',
        'location',
        'priority',
        'description',
        'image_path',
        'status',
        'admin_notes',
        'inspection_result',
        'parts_used',
        'repair_detail',
        'assigned_at',
        'completed_at',
    ];

    /**
     * กำหนดการแปลงชนิดข้อมูลอัตโนมัติ
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * ความสัมพันธ์: ผู้แจ้งซ่อม (เชื่อมกับ Model User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ความสัมพันธ์: ช่างผู้รับผิดชอบงานซ่อม (เชื่อมกับ Model User)
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * ความสัมพันธ์: อุปกรณ์ที่แจ้งซ่อม (เชื่อมกับ Model Equipment)
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Helper สำหรับสร้างเลขที่ใบแจ้งซ่อมแบบอัตโนมัติ
     * รูปแบบ: RE-001, RE-002 ... หรือ RE-YYMM-XXXX
     */
    public static function generateTrackingNumber(): string
    {
        $lastRecord = self::latest('id')->first();
        $nextId = $lastRecord ? ($lastRecord->id + 1) : 1;
        return sprintf('RE-%03d', $nextId);
    }

    /**
     * คืนค่า CSS Class สำหรับแสดงสีของ Status Badge ตามข้อกำหนดในข้อ 4:
     * - รอดำเนินการ: โทนเหลือง (waiting)
     * - รับเรื่องแล้ว / กำลังตรวจสอบ / กำลังซ่อม: โทนน้ำเงิน (working)
     * - ซ่อมเสร็จ: โทนเขียว (success)
     * - ยกเลิก: โทนแดง (cancelled)
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'รอดำเนินการ' => 'status-waiting',
            'รับเรื่องแล้ว' => 'status-working',
            'กำลังตรวจสอบ' => 'status-working',
            'กำลังซ่อม' => 'status-working',
            'ซ่อมเสร็จ' => 'status-success',
            'ยกเลิก' => 'status-cancelled',
            default => 'status-waiting',
        };
    }

    /**
     * คืนค่า CSS Class สำหรับสีของ Priority Badge
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'เร่งด่วน' => 'priority-urgent',
            'สูง' => 'priority-high',
            'ปานกลาง' => 'priority-medium',
            'ต่ำ' => 'priority-low',
            default => 'priority-medium',
        };
    }
}
