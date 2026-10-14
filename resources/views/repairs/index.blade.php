@extends('layouts.app')

@section('title', 'รายการแจ้งซ่อม | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท')

@section('content')
<div class="card">
    {{-- ส่วนหัวของหน้าและปุ่มสร้างงานใหม่ --}}
    <div class="card-header">
        <div>
            <h1>รายการแจ้งซ่อมเครื่องใช้ไฟฟ้า</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                @if(Auth::user()->isUser())
                    ประวัติและรายการแจ้งซ่อมเครื่องใช้ไฟฟ้าของคุณทั้งหมด
                @else
                    รายการแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท
                @endif
            </p>
        </div>
        <a href="{{ route('repairs.create') }}" class="btn btn-primary">
            + แจ้งซ่อมใหม่
        </a>
    </div>

    {{-- แถบตัวกรองและค้นหาข้อมูล (Filter & Search Form) --}}
    <form action="{{ route('repairs.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        {{-- ช่องค้นหาคำสำคัญ --}}
        <div style="flex: 1; min-width: 220px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 ค้นหาเลขที่, ชื่ออุปกรณ์, สถานที่..."
            >
        </div>

        {{-- ตัวกรองสถานะ --}}
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

        {{-- ตัวกรองระดับความเร่งด่วน --}}
        <div style="width: 170px;">
            <select name="priority" onchange="this.form.submit()">
                <option value="">-- ความเร่งด่วน --</option>
                <option value="ต่ำ" {{ request('priority') == 'ต่ำ' ? 'selected' : '' }}>ต่ำ</option>
                <option value="ปานกลาง" {{ request('priority') == 'ปานกลาง' ? 'selected' : '' }}>ปานกลาง</option>
                <option value="สูง" {{ request('priority') == 'สูง' ? 'selected' : '' }}>สูง</option>
                <option value="เร่งด่วน" {{ request('priority') == 'เร่งด่วน' ? 'selected' : '' }}>เร่งด่วน</option>
            </select>
        </div>

        {{-- ปุ่มล้างการค้นหา --}}
        @if(request()->hasAny(['search', 'status', 'priority']))
            <a href="{{ route('repairs.index') }}" class="btn btn-secondary">
                ล้างตัวกรอง
            </a>
        @endif
    </form>

    {{-- ตารางแสดงรายการแจ้งซ่อม --}}
    @if($repairs->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
            <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
            <p>ไม่พบรายการแจ้งซ่อมที่ตรงกับเงื่อนไข</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>อุปกรณ์</th>
                        <th>ประเภท</th>
                        <th>สถานที่</th>
                        <th>ความเร่งด่วน</th>
                        <th>สถานะ</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>วันที่แจ้ง</th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repairs as $repair)
                        <tr>
                            <td style="font-weight: 700; color: #60a5fa;">{{ $repair->tracking_number }}</td>
                            <td>{{ $repair->equipment_name }}</td>
                            <td><span style="color: #9ca3af; font-size: 13px;">{{ $repair->equipment_type }}</span></td>
                            <td>{{ $repair->location }}</td>
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
                                    <span style="color: #6b7280; font-style: italic;">รอแอดมินมอบหมาย</span>
                                @endif
                            </td>
                            <td style="color: #9ca3af; font-size: 13px;">
                                {{ $repair->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                    ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ส่วนแบ่งหน้า (Pagination Links) --}}
        <div class="pagination-box">
            {{ $repairs->links() }}
        </div>
    @endif
</div>
@endsection