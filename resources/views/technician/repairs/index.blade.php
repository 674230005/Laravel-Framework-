@extends('layouts.app')

@section('title', 'งานซ่อมที่ได้รับมอบหมาย (ช่างซ่อม) | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1>🛠️ งานซ่อมที่ฉันรับผิดชอบ (สำหรับช่างซ่อม)</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                รายการงานซ่อมที่คุณได้รับมอบหมายจากผู้ดูแลระบบ บันทึกความคืบหน้า และปิดงานเมื่อซ่อมเสร็จ
            </p>
        </div>
    </div>

    {{-- แถบตัวกรองสถานะและค้นหา --}}
    <form action="{{ route('technician.repairs.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <div style="flex: 1; min-width: 220px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 ค้นหาเลขที่, ชื่ออุปกรณ์, สถานที่..."
            >
        </div>

        <div style="width: 190px;">
            <select name="status" onchange="this.form.submit()">
                <option value="">-- ทุกสถานะ --</option>
                <option value="รับเรื่องแล้ว" {{ request('status') == 'รับเรื่องแล้ว' ? 'selected' : '' }}>รับเรื่องแล้ว</option>
                <option value="กำลังตรวจสอบ" {{ request('status') == 'กำลังตรวจสอบ' ? 'selected' : '' }}>กำลังตรวจสอบ</option>
                <option value="กำลังซ่อม" {{ request('status') == 'กำลังซ่อม' ? 'selected' : '' }}>กำลังซ่อม</option>
                <option value="ซ่อมเสร็จ" {{ request('status') == 'ซ่อมเสร็จ' ? 'selected' : '' }}>ซ่อมเสร็จแล้ว (ประวัติ)</option>
            </select>
        </div>

        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('technician.repairs.index') }}" class="btn btn-secondary">
                ล้างตัวกรอง
            </a>
        @endif
    </form>

    {{-- ตารางงานที่ช่างได้รับมอบหมาย --}}
    @if($repairs->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
            <div style="font-size: 38px; margin-bottom: 8px;">🎉</div>
            <p>ขณะนี้ไม่มีงานซ่อมค้างที่มอบหมายให้คุณ หรือไม่พบตามเงื่อนไขที่เลือก</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>อุปกรณ์</th>
                        <th>สถานที่</th>
                        <th>ผู้แจ้ง</th>
                        <th>ความเร่งด่วน</th>
                        <th>สถานะปัจจุบัน</th>
                        <th>วันที่ได้รับมอบหมาย</th>
                        <th style="text-align: center;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repairs as $repair)
                        <tr>
                            <td style="font-weight: 700; color: #60a5fa;">{{ $repair->tracking_number }}</td>
                            <td>{{ $repair->equipment_name }}</td>
                            <td>{{ $repair->location }}</td>
                            <td>{{ $repair->reporter_name }}</td>
                            <td>
                                <span class="priority-badge {{ $repair->priority_badge_class }}">
                                    {{ $repair->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $repair->status_badge_class }}">
                                    {{ $repair->status }}
                                </span>
                            </td>
                            <td style="color: #9ca3af; font-size: 13px;">
                                {{ $repair->assigned_at ? $repair->assigned_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('technician.repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                    🛠️ บันทึกงานซ่อม
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-box">
            {{ $repairs->links() }}
        </div>
    @endif
</div>
@endsection
