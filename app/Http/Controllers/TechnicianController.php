<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller สำหรับช่างซ่อม (TechnicianController)
 * จัดการกระบวนการทำงานของช่าง:
 * 1. ดูเฉพาะงานที่ตนเองได้รับมอบหมาย
 * 2. รับงาน และเปลี่ยนสถานะตามขั้นตอน (รับเรื่องแล้ว -> กำลังตรวจสอบ -> กำลังซ่อม -> ซ่อมเสร็จ)
 * 3. บันทึกอาการเสียที่พบจริง (inspection_result)
 * 4. บันทึกอะไหล่ที่เปลี่ยน (parts_used)
 * 5. บันทึกผลการซ่อมและวิธีแก้ไข (repair_detail)
 * 6. ปิดงานเมื่อซ่อมเสร็จสมบูรณ์
 * 7. ดูประวัติงานที่เคยรับผิดชอบ
 */
class TechnicianController extends Controller
{
    /**
     * แสดงรายการงานซ่อมที่มอบหมายให้ช่างคนปัจจุบัน
     */
    public function index(Request $request): View
    {
        $technician = Auth::user();

        $query = RepairRequest::with(['user', 'equipment'])
            ->where('technician_id', $technician->id);

        // กรองตามสถานะ
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // ค้นหาตามคำค้น
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('reporter_name', 'like', "%{$search}%");
            });
        }

        $repairs = $query->latest()->paginate(10)->withQueryString();

        return view('technician.repairs.index', compact('repairs'));
    }

    /**
     * แสดงรายละเอียดงานซ่อมสำหรับช่าง และฟอร์มบันทึกผลการซ่อม
     */
    public function show(RepairRequest $repair): View|RedirectResponse
    {
        $technician = Auth::user();

        // ตรวจสอบสิทธิ์: ช่างดูได้เฉพาะงานที่ได้รับมอบหมายเท่านั้น
        if ($repair->technician_id !== $technician->id && !$technician->isAdmin()) {
            return redirect()->route('technician.repairs.index')
                ->with('error', 'คุณสามารถเข้าถึงได้เฉพาะงานซ่อมที่ได้รับมอบหมายเท่านั้น');
        }

        $repair->load(['user', 'equipment']);

        return view('technician.repairs.show', compact('repair'));
    }

    /**
     * อัปเดตสถานะและบันทึกผลการซ่อม อะไหล่ที่เปลี่ยน และปิดงาน
     */
    public function update(Request $request, RepairRequest $repair): RedirectResponse
    {
        $technician = Auth::user();

        // ตรวจสอบสิทธิ์ช่าง
        if ($repair->technician_id !== $technician->id && !$technician->isAdmin()) {
            return redirect()->route('technician.repairs.index')
                ->with('error', 'คุณไม่มีสิทธิ์แก้ไขงานซ่อมนี้');
        }

        // ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $validated = $request->validate([
            'status' => ['required', 'in:รับเรื่องแล้ว,กำลังตรวจสอบ,กำลังซ่อม,ซ่อมเสร็จ,ยกเลิก'],
            'inspection_result' => ['nullable', 'string', 'max:2000'],
            'parts_used' => ['nullable', 'string', 'max:2000'],
            'repair_detail' => ['nullable', 'string', 'max:3000'],
        ], [
            'status.required' => 'กรุณาเลือกสถานะการดำเนินงาน',
            'status.in' => 'สถานะที่เลือกไม่ถูกต้อง',
        ]);

        // บันทึกข้อมูลผลการปฏิบัติงาน
        $repair->status = $validated['status'];
        $repair->inspection_result = $validated['inspection_result'] ?? $repair->inspection_result;
        $repair->parts_used = $validated['parts_used'] ?? $repair->parts_used;
        $repair->repair_detail = $validated['repair_detail'] ?? $repair->repair_detail;

        // หากสถานะเป็น "ซ่อมเสร็จ" ให้ลงเวลาเสร็จสิ้นโดยอัตโนมัติ
        if ($validated['status'] === 'ซ่อมเสร็จ' && !$repair->completed_at) {
            $repair->completed_at = now();
        }

        $repair->save();

        return redirect()->route('technician.repairs.show', $repair)
            ->with('success', 'บันทึกข้อมูลผลการซ่อมและอัปเดตสถานะเรียบร้อยแล้ว');
    }
}
