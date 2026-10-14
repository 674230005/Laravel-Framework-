<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Equipment: จัดการข้อมูลครุภัณฑ์/เครื่องใช้ไฟฟ้าในบริษัท
 * ใช้สำหรับจัดเก็บและตรวจสอบข้อมูลอุปกรณ์ เช่น แอร์ คอมพิวเตอร์ เครื่องพิมพ์
 */
class Equipment extends Model
{
    use HasFactory;

    /**
     * ชื่อตารางในฐานข้อมูล
     */
    protected $table = 'equipment';

    /**
     * ฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'location',
        'status',
        'description',
    ];

    /**
     * ความสัมพันธ์: รายการแจ้งซ่อมของอุปกรณ์นี้ (1 อุปกรณ์มีประวัติแจ้งซ่อมได้หลายครั้ง)
     */
    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'equipment_id');
    }
}
