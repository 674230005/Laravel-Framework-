<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (เส้นทางของระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท)
|--------------------------------------------------------------------------
| กำหนดเส้นทาง URL ทั้งหมดในระบบ แบ่งตามสิทธิ์และบทบาท:
| 1. เส้นทางสาธารณะ และการยืนยันตัวตน (Login / Logout)
| 2. เส้นทางสำหรับผู้ใช้งานที่เข้าสู่ระบบแล้ว (Dashboard, แจ้งซ่อม, ติดตามสถานะ)
| 3. เส้นทางสำหรับช่างซ่อม (รับงาน, บันทึกผลการซ่อม, ปิดงาน)
| 4. เส้นทางสำหรับผู้ดูแลระบบ (มอบหมายงาน, จัดการผู้ใช้, อุปกรณ์, และรายงาน)
*/

// =========================================================================
// 1. เส้นทางหน้าแรกและการเข้าสู่ระบบ (Authentication)
// =========================================================================

// หน้าแรก: ตรวจสอบสถานะการล็อกอิน ถ้าล็อกอินแล้วส่งไป dashboard ถ้ายังให้ไป login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// หน้าฟอร์มเข้าสู่ระบบ
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// ประมวลผลการเข้าสู่ระบบเมื่อส่งฟอร์ม POST
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ออกจากระบบ (รองรับทั้ง POST และ GET เพื่อความสะดวกในการใช้งาน)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');


// =========================================================================
// 2. เส้นทางสำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว (Authenticated Users)
// =========================================================================
Route::middleware('auth')->group(function () {

    // หน้าหลัก / แผงควบคุมสรุปข้อมูล
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // รายการแจ้งซ่อม และการสร้างคำขอแจ้งซ่อมใหม่
    Route::get('/repairs', [RepairRequestController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairRequestController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [RepairRequestController::class, 'store'])->name('repairs.store');

    // หน้าติดตามสถานะงานซ่อม
    Route::get('/repairs/status', [RepairRequestController::class, 'status'])->name('repairs.status');

    // ดูรายละเอียดงานซ่อมรายใบ
    Route::get('/repairs/{repair}', [RepairRequestController::class, 'show'])->name('repairs.show');
});


// =========================================================================
// 3. เส้นทางสำหรับช่างซ่อม (Technician Role)
// =========================================================================
Route::middleware(['auth', 'role:technician,admin'])->prefix('technician')->name('technician.')->group(function () {

    // ดูรายการงานซ่อมที่ได้รับมอบหมาย
    Route::get('/repairs', [TechnicianController::class, 'index'])->name('repairs.index');

    // ดูรายละเอียดงานและเปิดฟอร์มบันทึกผลการซ่อม
    Route::get('/repairs/{repair}', [TechnicianController::class, 'show'])->name('repairs.show');

    // บันทึกผลการซ่อม อะไหล่ที่เปลี่ยน และอัปเดตสถานะ
    Route::post('/repairs/{repair}', [TechnicianController::class, 'update'])->name('repairs.update');
});


// =========================================================================
// 4. เส้นทางสำหรับผู้ดูแลระบบ (Admin Role)
// =========================================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // จัดการงานซ่อมทั้งหมดในระบบ
    Route::get('/repairs', [AdminController::class, 'repairsIndex'])->name('repairs.index');
    Route::get('/repairs/{repair}', [AdminController::class, 'repairShow'])->name('repairs.show');
    Route::post('/repairs/{repair}/assign', [AdminController::class, 'assignTechnician'])->name('repairs.assign');

    // จัดการบัญชีผู้ใช้และช่าง (User Management)
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'userCreate'])->name('users.create');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'userEdit'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');

    // รายงานสถิติงานซ่อมตามสถานะและช่วงเวลา (Reports)
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

    // จัดการข้อมูลเครื่องใช้ไฟฟ้า (Equipment Management)
    Route::get('/equipment', [AdminController::class, 'equipmentIndex'])->name('equipment.index');
    Route::post('/equipment', [AdminController::class, 'equipmentStore'])->name('equipment.store');
    Route::delete('/equipment/{equipment}', [AdminController::class, 'equipmentDestroy'])->name('equipment.destroy');
});