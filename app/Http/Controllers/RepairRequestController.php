<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\RepairRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller จัดการคำขอแจ้งซ่อม (Repair Requests)
 * รองรับ:
 * - การแสดงฟอร์มแจ้งซ่อม (create)
 * - การบันทึกคำขอแจ้งซ่อม พร้อมอัปโหลดรูปภาพและการตรวจสอบไฟล์อย่างปลอดภัย (store)
 * - การดูรายการแจ้งซ่อมและประวัติ (index)
 * - การดูรายละเอียดและติดตามสถานะงานซ่อมแบบเจาะลึก (show)
 * - หน้าติดตามสถานะงานซ่อม (status)
 */
class RepairRequestController extends Controller
{
    /**
     * แสดงรายการแจ้งซ่อม
     * - ผู้ใช้ทั่วไป: ดูเฉพาะรายการที่ตนเองแจ้ง
     * - ช่างซ่อม: ดูเฉพาะงานที่ได้รับมอบหมาย
     * - แอดมิน: ดูได้ทั้งหมด
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // สร้าง Query พื้นฐานพร้อมโหลดความสัมพันธ์ (Eager Loading) เพื่อลดจำนวน Database Query
        $query = RepairRequest::with(['user', 'technician', 'equipment']);

        // กรองตามสิทธิ์ของผู้ใช้งาน
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isTechnician()) {
            $query->where('technician_id', $user->id);
        }

        // ค้นหาตามคำค้น (รหัสใบแจ้งซ่อม, ชื่ออุปกรณ์ หรือสถานที่)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('reporter_name', 'like', "%{$search}%");
            });
        }

        // ตัวกรองสถานะ
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // ตัวกรองระดับความเร่งด่วน
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $repairs = $query->latest()->paginate(10)->withQueryString();

        return view('repairs.index', compact('repairs'));
    }

    /**
     * แสดงหน้าฟอร์มสำหรับแจ้งซ่อมเครื่องใช้ไฟฟ้า
     */
    public function create(): View
    {
        // ดึงรายการเครื่องใช้ไฟฟ้าที่มีในระบบ เพื่ออำนวยความสะดวกในการเลือก
        $equipmentList = Equipment::orderBy('name')->get();

        return view('repairs.create', compact('equipmentList'));
    }

    /**
     * บันทึกข้อมูลการแจ้งซ่อมลงในฐานข้อมูล
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูลจากฟอร์ม (Validation)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'equipment' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:ต่ำ,ปานกลาง,สูง,เร่งด่วน'],
            'description' => ['required', 'string', 'max:3000'],
            'equipment_id' => ['nullable', 'exists:equipment,id'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // จำกัดขนาดไม่เกิน 5 MB
            ],
        ], [
            'name.required' => 'กรุณากรอกชื่อผู้แจ้ง',
            'equipment.required' => 'กรุณากรอกชื่ออุปกรณ์',
            'type.required' => 'กรุณาเลือกประเภทอุปกรณ์',
            'location.required' => 'กรุณากรอกสถานที่ตั้งอุปกรณ์',
            'priority.required' => 'กรุณาเลือกระดับความเร่งด่วน',
            'description.required' => 'กรุณากรอกอาการเสียหรือรายละเอียดปัญหา',
            'image.image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพเท่านั้น',
            'image.mimes' => 'รองรับเฉพาะไฟล์รูปภาพนามสกุล jpeg, png, jpg, webp เท่านั้น',
            'image.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 5 MB',
        ]);

        // 2. จัดการอัปโหลดรูปภาพอย่างปลอดภัย
        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // บันทึกไฟล์ลงใน storage/app/public/repairs
            $imagePath = $request->file('image')->store('repairs', 'public');
        }

        // 3. สร้างเลขที่ใบแจ้งซ่อมอัตโนมัติ เช่น RE-004
        $trackingNumber = RepairRequest::generateTrackingNumber();

        // 4. บันทึกข้อมูลลงตาราง repair_requests
        $repair = RepairRequest::create([
            'tracking_number' => $trackingNumber,
            'user_id' => Auth::id(),
            'equipment_id' => $validated['equipment_id'] ?? null,
            'reporter_name' => $validated['name'],
            'equipment_name' => $validated['equipment'],
            'equipment_type' => $validated['type'],
            'location' => $validated['location'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'status' => 'รอดำเนินการ', // สถานะเริ่มต้นเมื่อส่งฟอร์ม
        ]);

        return redirect()->route('repairs.show', $repair)
            ->with('success', "ส่งคำขอแจ้งซ่อมเรียบร้อยแล้ว เลขที่แจ้งซ่อมของคุณคือ {$trackingNumber}");
    }

    /**
     * แสดงรายละเอียดและติดตามสถานะงานซ่อมแบบเจาะลึก
     */
    public function show(RepairRequest $repair): View|RedirectResponse
    {
        $user = Auth::user();

        // ตรวจสอบสิทธิ์: ผู้ใช้ทั่วไปดูได้เฉพาะงานของตนเอง
        if ($user->isUser() && $repair->user_id !== $user->id) {
            return redirect()->route('repairs.index')
                ->with('error', 'คุณไม่มีสิทธิ์เข้าดูรายละเอียดงานซ่อมนี้');
        }

        // โหลดความสัมพันธ์ที่เกี่ยวข้อง
        $repair->load(['user', 'technician', 'equipment']);

        return view('repairs.show', compact('repair'));
    }

    /**
     * หน้าติดตามสถานะงานซ่อม (Status Tracking)
     * รองรับการค้นหาเลขที่แจ้งซ่อมและดูประวัติ
     */
    public function status(Request $request): View
    {
        $user = Auth::user();
        $query = RepairRequest::with(['user', 'technician']);

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isTechnician()) {
            $query->where('technician_id', $user->id);
        }

        if ($request->filled('tracking_number')) {
            $query->where('tracking_number', 'like', '%' . trim($request->input('tracking_number')) . '%');
        }

        $repairs = $query->latest()->paginate(10)->withQueryString();

        return view('repairs.status', compact('repairs'));
    }
}
