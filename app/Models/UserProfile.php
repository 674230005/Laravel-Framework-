<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model UserProfile: จัดเก็บข้อมูลโปรไฟล์เพิ่มเติมของผู้ใช้งาน
 * เพื่อให้สามารถเก็บข้อมูลแผนก ตำแหน่ง และเบอร์ติดต่อได้เป็นสัดส่วน
 */
class UserProfile extends Model
{
    use HasFactory;

    /**
     * ชื่อตารางในฐานข้อมูล
     */
    protected $table = 'user_profiles';

    /**
     * ฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'user_id',
        'phone',
        'department',
        'position',
    ];

    /**
     * ความสัมพันธ์: เป็นโปรไฟล์ของผู้ใช้คนใด (เป็นเจ้าของโดย 1 User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
