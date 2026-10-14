<?php

namespace App\Models;

// นำเข้า Traits และคลาสพื้นฐานสำหรับการยืนยันตัวตนของ Laravel
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User: จัดการข้อมูลผู้ใช้งานในระบบ
 * รองรับ 3 บทบาทหลัก:
 * - 'admin' = ผู้ดูแลระบบ (จัดการงาน มอบหมายช่าง ดูแลผู้ใช้ และดูรายงาน)
 * - 'technician' = ช่างซ่อม (รับงาน ตรวจสอบ อัปเดตสถานะ และบันทึกผลการซ่อม)
 * - 'user' = พนักงาน/ผู้ใช้ทั่วไป (แจ้งซ่อม ดูสถานะ และประวัติงานซ่อมของตนเอง)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * กำหนดฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'department',
    ];

    /**
     * กำหนดฟิลด์ที่ต้องซ่อนเมื่อแปลงข้อมูลเป็น Array หรือ JSON (เช่น ซ่อนรหัสผ่าน)
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * กำหนดการแปลงชนิดข้อมูลอัตโนมัติ (Casting)
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ความสัมพันธ์: รายการแจ้งซ่อมที่ผู้ใช้คนนี้เป็นผู้แจ้ง (1 ผู้ใช้มีได้หลายใบแจ้งซ่อม)
     */
    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'user_id');
    }

    /**
     * ความสัมพันธ์: งานซ่อมที่ช่างคนนี้ได้รับมอบหมาย (1 ช่างรับได้หลายงาน)
     */
    public function assignedJobs(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'technician_id');
    }

    /**
     * ความสัมพันธ์: ข้อมูลประวัติส่วนตัวเพิ่มเติม (1 ผู้ใช้มี 1 โปรไฟล์)
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * ฟังก์ชันตรวจสอบว่าผู้ใช้เป็น Admin หรือไม่
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * ฟังก์ชันตรวจสอบว่าผู้ใช้เป็นช่างซ่อมหรือไม่
     */
    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    /**
     * ฟังก์ชันตรวจสอบว่าผู้ใช้เป็นผู้ใช้ทั่วไปหรือไม่
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * ฟังก์ชันแปลงชื่อบทบาทเป็นภาษาไทยสำหรับแสดงผลบนหน้าจอ
     */
    public function getRoleNameThaiAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'ผู้ดูแลระบบ (Admin)',
            'technician' => 'ช่างซ่อม (Technician)',
            'user' => 'ผู้ใช้งานทั่วไป (User)',
            default => 'ผู้ใช้งาน',
        };
    }
}
