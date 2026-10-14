<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller สำหรับหน้า Dashboard
 * แสดงข้อมูลสรุปสถิติและรายการงานซ่อมล่าสุดที่เหมาะสมกับบทบาทของผู้ใช้แต่ละคน
 */
class DashboardController extends Controller
{
    /**
     * ประมวลผลและแสดงหน้า Dashboard ตามบทบาทของผู้ใช้งาน
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. สรุปสถิติสำหรับผู้ดูแลระบบ (Admin)
        if ($user->isAdmin()) {
            $stats = [
                'total_repairs' => RepairRequest::count(),
                'pending' => RepairRequest::where('status', 'รอดำเนินการ')->count(),
                'working' => RepairRequest::whereIn('status', ['รับเรื่องแล้ว', 'กำลังตรวจสอบ', 'กำลังซ่อม'])->count(),
                'completed' => RepairRequest::where('status', 'ซ่อมเสร็จ')->count(),
                'cancelled' => RepairRequest::where('status', 'ยกเลิก')->count(),
                'total_users' => User::where('role', 'user')->count(),
                'total_technicians' => User::where('role', 'technician')->count(),
                'total_equipment' => Equipment::count(),
            ];

            // ดึงรายการแจ้งซ่อม 5 รายการล่าสุด
            $recentRepairs = RepairRequest::with(['user', 'technician'])
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', compact('user', 'stats', 'recentRepairs'));
        }

        // 2. สรุปสถิติสำหรับช่างซ่อม (Technician)
        if ($user->isTechnician()) {
            $stats = [
                'assigned_total' => RepairRequest::where('technician_id', $user->id)->count(),
                'assigned_pending' => RepairRequest::where('technician_id', $user->id)
                    ->whereIn('status', ['รับเรื่องแล้ว', 'กำลังตรวจสอบ'])->count(),
                'assigned_in_progress' => RepairRequest::where('technician_id', $user->id)
                    ->where('status', 'กำลังซ่อม')->count(),
                'assigned_completed' => RepairRequest::where('technician_id', $user->id)
                    ->where('status', 'ซ่อมเสร็จ')->count(),
            ];

            // งานที่ช่างคนนี้รับผิดชอบ 5 รายการล่าสุด
            $recentRepairs = RepairRequest::with(['user'])
                ->where('technician_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', compact('user', 'stats', 'recentRepairs'));
        }

        // 3. สรุปสถิติสำหรับพนักงานทั่วไป (User)
        $stats = [
            'my_total' => RepairRequest::where('user_id', $user->id)->count(),
            'my_pending' => RepairRequest::where('user_id', $user->id)
                ->where('status', 'รอดำเนินการ')->count(),
            'my_working' => RepairRequest::where('user_id', $user->id)
                ->whereIn('status', ['รับเรื่องแล้ว', 'กำลังตรวจสอบ', 'กำลังซ่อม'])->count(),
            'my_completed' => RepairRequest::where('user_id', $user->id)
                ->where('status', 'ซ่อมเสร็จ')->count(),
        ];

        // รายการแจ้งซ่อมของตนเอง 5 รายการล่าสุด
        $recentRepairs = RepairRequest::with(['technician'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('user', 'stats', 'recentRepairs'));
    }
}
