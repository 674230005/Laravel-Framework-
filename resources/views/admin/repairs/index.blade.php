@extends('layouts.app')

@section('title', 'จัดการงานซ่อมทั้งหมด (Admin) | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1>⚙️ จัดการงานแจ้งซ่อมทั้งหมด (สำหรับแอดมิน)</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                ตรวจสอบคำขอแจ้งซ่อมทั้งหมด มอบหมายงานให้ช่าง และติดตามสถานะความคืบหน้า
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
                📈 ดูรายงานสรุป
            </a>
            <a href="{{ route('repairs.create') }}" class="btn btn-primary btn-sm">
                + เพิ่มการแจ้งซ่อม
            </a>
        </div>
    </div>

    {{-- แถบตัวกรองและค้นหา --}}
    <form action="{{ route('admin.repairs.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <div style="flex: 1; min-width: 200px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 ค้นหาเลขที่, อุปกรณ์, ผู้แจ้ง, สถานที่..."
            >
        </div>

        <div style="width: 170px;">
            <select name="status" onchange="this.form.submit()">
                <option value="">-- สถานะทั้งหมด --</option>
                <option value="รอดำเนินการ" {{ request('status') == 'รอดำเนินการ' ? 'selected' : '' }}>รอดำเนินการ</option>
                <option value="รับเรื่องแล้ว" {{ request('status') == 'รับเรื่องแล้ว' ? 'selected' : '' }}>รับเรื่องแล้ว</option>
                <option value="กำลังตรวจสอบ" {{ request('status') == 'กำลังตรวจสอบ' ? 'selected' : '' }}>กำลังตรวจสอบ</option>
                <option value="กำลังซ่อม" {{ request('status') == 'กำลังซ่อม' ? 'selected' : '' }}>กำลังซ่อม</option>
                <option value="ซ่อมเสร็จ" {{ request('status') == 'ซ่อมเสร็จ' ? 'selected' : '' }}>ซ่อมเสร็จ</option>
                <option value="ยกเลิก" {{ request('status') == 'ยกเลิก' ? 'selected' : '' }}>ยกเลิก</option>
            </select>
        </div>

        <div style="width: 190px;">
            <select name="technician_id" onchange="this.form.submit()">
                <option value="">-- กรองตามช่าง --</option>
                <option value="unassigned" {{ request('technician_id') == 'unassigned' ? 'selected' : '' }}>⚠️ ยังไม่ได้มอบหมายช่าง</option>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                        {{ $tech->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if(request()->hasAny(['search', 'status', 'technician_id']))
            <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">
                ล้างตัวกรอง
            </a>
        @endif
    </form>

    {{-- ตารางงานซ่อมทั้งหมด --}}
    @if($repairs->isEmpty())
        <div style="text-align: center; padding: 40px; color: #9ca3af;">
            <div style="font-size: 38px; margin-bottom: 8px;">📭</div>
            <p>ไม่พบรายการงานซ่อมตามเงื่อนไขที่เลือก</p>
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
                        <th>สถานะ</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>วันที่แจ้ง</th>
                        <th style="text-align: center;">การจัดการ</th>
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
                            <td>
                                @if($repair->technician)
                                    <span style="color: #93c5fd;">{{ $repair->technician->name }}</span>
                                @else
                                    <span style="color: #fbbf24; font-weight: 500;">⚠️ ยังไม่มอบหมาย</span>
                                @endif
                            </td>
                            <td style="color: #9ca3af; font-size: 13px;">
                                {{ $repair->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                    👨‍🔧 มอบหมาย/ดูงาน
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
