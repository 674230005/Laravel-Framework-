<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller สำหรับผู้ดูแลระบบ (AdminController)
 * ควบคุมและบริหารจัดการระบบทั้งหมด ได้แก่:
 * 1. จัดการและมอบหมายงานซ่อมให้ช่าง (Assign / Reassign / Unassign)
 * 2. จัดการบัญชีผู้ใช้และช่าง (User & Technician Management)
 * 3. จัดการข้อมูลเครื่องใช้ไฟฟ้า (Equipment Management)
 * 4. รายงานสรุปงานซ่อมตามสถานะและช่วงเวลา (Reports & Analytics)
 */
class AdminController extends Controller
{
    /**
     * แสดงรายการแจ้งซ่อมทั้งหมดในระบบ พร้อมตัวกรองและการค้นหา
     */
    public function repairsIndex(Request $request): View
    {
        $query = RepairRequest::with(['user', 'technician', 'equipment']);

        // กรองตามสถานะ
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // กรองตามช่างผู้รับผิดชอบ
        if ($request->filled('technician_id')) {
            if ($request->input('technician_id') === 'unassigned') {
                $query->whereNull('technician_id');
            } else {
                $query->where('technician_id', $request->input('technician_id'));
            }
        }

        // ค้นหาตามคำค้น
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('reporter_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $repairs = $query->latest()->paginate(10)->withQueryString();
        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('admin.repairs.index', compact('repairs', 'technicians'));
    }

    /**
     * แสดงรายละเอียดงานซ่อมสำหรับแอดมิน เพื่อตรวจสอบและมอบหมายงาน
     */
    public function repairShow(RepairRequest $repair): View
    {
        $repair->load(['user', 'technician', 'equipment']);
        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('admin.repairs.show', compact('repair', 'technicians'));
    }

    /**
     * มอบหมายงานให้ช่าง หรือเปลี่ยน/ยกเลิกการมอบหมายงาน
     */
    public function assignTechnician(Request $request, RepairRequest $repair): RedirectResponse
    {
        $validated = $request->validate([
            'technician_id' => ['nullable', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:รอดำเนินการ,รับเรื่องแล้ว,กำลังตรวจสอบ,กำลังซ่อม,ซ่อมเสร็จ,ยกเลิก'],
        ], [
            'technician_id.exists' => 'ไม่พบข้อมูลช่างที่เลือกในระบบ',
            'status.required' => 'กรุณาระบุสถานะของงาน',
        ]);

        $repair->technician_id = $validated['technician_id'] ?? null;
        $repair->admin_notes = $validated['admin_notes'] ?? $repair->admin_notes;
        $repair->status = $validated['status'];

        // ถ้ามีการมอบหมายช่างเป็นครั้งแรกและยังไม่มีเวลา ให้บันทึกเวลาที่มอบหมาย
        if ($repair->technician_id && !$repair->assigned_at) {
            $repair->assigned_at = now();
        }

        // ถ้าตั้งสถานะเป็นซ่อมเสร็จ ให้ลงเวลาเสร็จสิ้นหากยังไม่มี
        if ($repair->status === 'ซ่อมเสร็จ' && !$repair->completed_at) {
            $repair->completed_at = now();
        }

        $repair->save();

        return back()->with('success', 'บันทึกการมอบหมายงานและปรับปรุงสถานะเรียบร้อยแล้ว');
    }

    /**
     * รายการจัดการบัญชีผู้ใช้และช่าง (User Management)
     */
    public function usersIndex(Request $request): View
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * แสดงแบบฟอร์มสร้างบัญชีผู้ใช้ใหม่
     */
    public function userCreate(): View
    {
        return view('admin.users.create');
    }

    /**
     * บันทึกบัญชีผู้ใช้ใหม่
     */
    public function userStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:user,technician,admin'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีอยู่ในระบบแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'role.required' => 'กรุณาเลือกบทบาทผู้ใช้',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'เพิ่มผู้ใช้งานใหม่เรียบร้อยแล้ว');
    }

    /**
     * แสดงฟอร์มแก้ไขบัญชีผู้ใช้
     */
    public function userEdit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * อัปเดตข้อมูลบัญชีผู้ใช้
     */
    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:user,technician,admin'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'role.required' => 'กรุณาเลือกบทบาทผู้ใช้',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->department = $validated['department'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'ปรับปรุงข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * ลบบัญชีผู้ใช้
     */
    public function userDestroy(User $user): RedirectResponse
    {
        // ป้องกันไม่ให้แอดมินลบบัญชีตัวเอง
        if ($user->id === auth()->id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตนเองที่กำลังเข้าสู่ระบบอยู่ได้');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว');
    }

    /**
     * หน้ารายงานสรุปงานซ่อมตามสถานะและช่วงเวลา (Reports)
     */
    public function reports(Request $request): View
    {
        $query = RepairRequest::with(['user', 'technician']);

        // กรองตามสถานะ
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // กรองตามช่วงเวลาเริ่มต้น (จากวันที่)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        // กรองตามช่วงเวลาสิ้นสุด (ถึงวันที่)
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $repairs = $query->latest()->paginate(15)->withQueryString();

        // คำนวณสรุปสถิติตามเงื่อนไข
        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'รอดำเนินการ')->count(),
            'working' => (clone $query)->whereIn('status', ['รับเรื่องแล้ว', 'กำลังตรวจสอบ', 'กำลังซ่อม'])->count(),
            'completed' => (clone $query)->where('status', 'ซ่อมเสร็จ')->count(),
            'cancelled' => (clone $query)->where('status', 'ยกเลิก')->count(),
        ];

        return view('admin.reports.index', compact('repairs', 'summary'));
    }

    /**
     * แสดงรายการและจัดการเครื่องใช้ไฟฟ้า (Equipment Management)
     */
    public function equipmentIndex(Request $request): View
    {
        $query = Equipment::withCount('repairRequests');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $equipmentList = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.equipment.index', compact('equipmentList'));
    }

    /**
     * บันทึกข้อมูลเครื่องใช้ไฟฟ้าใหม่
     */
    public function equipmentStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:equipment'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:ปกติ,ชำรุด,กำลังซ่อม,ปลดระวาง'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'กรุณากรอกรหัสอุปกรณ์',
            'code.unique' => 'รหัสอุปกรณ์นี้มีอยู่ในระบบแล้ว',
            'name.required' => 'กรุณากรอกชื่ออุปกรณ์',
            'type.required' => 'กรุณาเลือกประเภทอุปกรณ์',
            'location.required' => 'กรุณากรอกสถานที่ติดตั้ง',
        ]);

        Equipment::create($validated);

        return back()->with('success', 'เพิ่มอุปกรณ์ใหม่เรียบร้อยแล้ว');
    }

    /**
     * ลบข้อมูลเครื่องใช้ไฟฟ้า
     */
    public function equipmentDestroy(Equipment $equipment): RedirectResponse
    {
        $equipment->delete();

        return back()->with('success', 'ลบอุปกรณ์เรียบร้อยแล้ว');
    }
}
