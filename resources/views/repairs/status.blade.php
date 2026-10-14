@extends('layouts.app')

@section('title', 'ติดตามสถานะงานซ่อม | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1>ติดตามสถานะงานซ่อม</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                ตรวจสอบความคืบหน้าการแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัทแบบเรียลไทม์
            </p>
        </div>
        <a href="{{ route('repairs.create') }}" class="btn btn-primary">
            + แจ้งซ่อมใหม่
        </a>
    </div>

    {{-- ฟอร์มค้นหาตามเลขที่แจ้งซ่อม --}}
    <form action="{{ route('repairs.status') }}" method="GET" style="display: flex; gap: 10px; margin-bottom: 25px;">
        <input
            type="text"
            name="tracking_number"
            value="{{ request('tracking_number') }}"
            placeholder="🔎 กรอกเลขที่แจ้งซ่อม เช่น RE-001 เพื่อค้นหาอย่างรวดเร็ว"
            style="flex: 1;"
        >
        <button type="submit" class="btn btn-primary">
            ค้นหาสถานะ
        </button>
        @if(request()->filled('tracking_number'))
            <a href="{{ route('repairs.status') }}" class="btn btn-secondary">
                แสดงทั้งหมด
            </a>
        @endif
    </form>

    {{-- รายการการ์ดงานซ่อม (เชื่อมต่อกับฐานข้อมูลจริง) --}}
    @if($repairs->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
            <div style="font-size: 40px; margin-bottom: 10px;">🔍</div>
            <p>ไม่พบรายการแจ้งซ่อมตามเลขที่ค้นหา</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 18px;">
            @foreach($repairs as $repair)
                <div style="background: #111827; border: 1px solid #1e3a8a; border-radius: 10px; padding: 22px;">
                    {{-- ส่วนหัวของการ์ดแสดงเลขที่และสถานะ --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid #1f2937; padding-bottom: 12px;">
                        <div>
                            <span style="color: #60a5fa; font-size: 18px; font-weight: bold;">
                                เลขที่ {{ $repair->tracking_number }}
                            </span>
                            <span style="color: #9ca3af; font-size: 13px; margin-left: 10px;">
                                แจ้งเมื่อ: {{ $repair->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="priority-badge {{ $repair->priority_badge_class }}">
                                ความเร่งด่วน: {{ $repair->priority }}
                            </span>
                            <span class="status-badge {{ $repair->status_badge_class }}">
                                {{ $repair->status }}
                            </span>
                        </div>
                    </div>

                    {{-- รายละเอียดข้อมูลของงานซ่อม --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div style="background: #0f172a; padding: 12px 15px; border-radius: 6px; border: 1px solid #1f2937;">
                            <div style="color: #9ca3af; font-size: 13px; margin-bottom: 4px;">อุปกรณ์</div>
                            <div style="color: #ffffff; font-weight: 500;">{{ $repair->equipment_name }} ({{ $repair->equipment_type }})</div>
                        </div>

                        <div style="background: #0f172a; padding: 12px 15px; border-radius: 6px; border: 1px solid #1f2937;">
                            <div style="color: #9ca3af; font-size: 13px; margin-bottom: 4px;">สถานที่ตั้ง</div>
                            <div style="color: #ffffff; font-weight: 500;">{{ $repair->location }}</div>
                        </div>

                        <div style="background: #0f172a; padding: 12px 15px; border-radius: 6px; border: 1px solid #1f2937;">
                            <div style="color: #9ca3af; font-size: 13px; margin-bottom: 4px;">ช่างผู้รับผิดชอบ</div>
                            <div style="color: #93c5fd; font-weight: 500;">
                                {{ $repair->technician ? $repair->technician->name : 'รอแอดมินมอบหมายงาน' }}
                            </div>
                        </div>

                        <div style="background: #0f172a; padding: 12px 15px; border-radius: 6px; border: 1px solid #1f2937;">
                            <div style="color: #9ca3af; font-size: 13px; margin-bottom: 4px;">ผลการดำเนินงาน</div>
                            <div style="color: #dbeafe; font-size: 14px;">
                                @if($repair->status === 'ซ่อมเสร็จ')
                                    <span style="color: #86efac;">{{ $repair->repair_detail ?: 'ซ่อมแซมและทดสอบระบบเรียบร้อยแล้ว' }}</span>
                                @elseif($repair->status === 'กำลังซ่อม')
                                    <span style="color: #93c5fd;">{{ $repair->repair_detail ?: 'อยู่ระหว่างดำเนินการซ่อมแซม' }}</span>
                                @elseif($repair->status === 'กำลังตรวจสอบ')
                                    <span style="color: #fbbf24;">{{ $repair->inspection_result ?: 'อยู่ระหว่างตรวจสอบหาสาเหตุ' }}</span>
                                @elseif($repair->status === 'ยกเลิก')
                                    <span style="color: #fca5a5;">รายการแจ้งซ่อมถูกยกเลิก</span>
                                @else
                                    <span style="color: #9ca3af;">รอช่างตรวจสอบรายการแจ้งซ่อม</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ปุ่มไปยังหน้ารายละเอียดเต็ม --}}
                    <div style="text-align: right; margin-top: 10px;">
                        <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary btn-sm">
                            ดูรายละเอียดและรูปภาพทั้งหมด &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ส่วนแบ่งหน้า --}}
        <div class="pagination-box">
            {{ $repairs->links() }}
        </div>
    @endif
</div>
@endsection