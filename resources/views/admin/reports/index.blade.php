@extends('layouts.app')

@section('title', 'รายงานสรุปงานซ่อม (Admin) | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1>📈 รายงานสรุปงานซ่อมตามสถานะและช่วงเวลา</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                สรุปสถิติงานซ่อมเครื่องใช้ไฟฟ้า สามารถกรองตามสถานะและช่วงวันที่ตามต้องการ
            </p>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
            🖨️ พิมพ์รายงาน
        </button>
    </div>

    {{-- แถบตัวกรองรายงานตามสถานะและช่วงเวลา --}}
    <form action="{{ route('admin.reports.index') }}" method="GET" style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1e3a8a; margin-bottom: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">

            <div>
                <label for="status" style="font-size: 13px;">สถานะงานซ่อม</label>
                <select id="status" name="status">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="รอดำเนินการ" {{ request('status') == 'รอดำเนินการ' ? 'selected' : '' }}>รอดำเนินการ</option>
                    <option value="รับเรื่องแล้ว" {{ request('status') == 'รับเรื่องแล้ว' ? 'selected' : '' }}>รับเรื่องแล้ว</option>
                    <option value="กำลังตรวจสอบ" {{ request('status') == 'กำลังตรวจสอบ' ? 'selected' : '' }}>กำลังตรวจสอบ</option>
                    <option value="กำลังซ่อม" {{ request('status') == 'กำลังซ่อม' ? 'selected' : '' }}>กำลังซ่อม</option>
                    <option value="ซ่อมเสร็จ" {{ request('status') == 'ซ่อมเสร็จ' ? 'selected' : '' }}>ซ่อมเสร็จ</option>
                    <option value="ยกเลิก" {{ request('status') == 'ยกเลิก' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>

            <div>
                <label for="date_from" style="font-size: 13px;">ตั้งแต่วันที่</label>
                <input
                    type="date"
                    id="date_from"
                    name="date_from"
                    value="{{ request('date_from') }}"
                >
            </div>

            <div>
                <label for="date_to" style="font-size: 13px;">ถึงวันที่</label>
                <input
                    type="date"
                    id="date_to"
                    name="date_to"
                    value="{{ request('date_to') }}"
                >
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    🔍 ค้นหารายงาน
                </button>
                @if(request()->hasAny(['status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                        รีเซ็ต
                    </a>
                @endif
            </div>

        </div>
    </form>

    {{-- การ์ดสรุปยอดรวมตามเงื่อนไขที่เลือก --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #1e3a8a; text-align: center;">
            <div style="color: #9ca3af; font-size: 13px;">งานทั้งหมดตามเงื่อนไข</div>
            <div style="color: #60a5fa; font-size: 24px; font-weight: bold; margin-top: 5px;">{{ $summary['total'] }} งาน</div>
        </div>

        <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #78350f; text-align: center;">
            <div style="color: #9ca3af; font-size: 13px;">รอดำเนินการ</div>
            <div style="color: #fbbf24; font-size: 24px; font-weight: bold; margin-top: 5px;">{{ $summary['pending'] }} งาน</div>
        </div>

        <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #1e3a8a; text-align: center;">
            <div style="color: #9ca3af; font-size: 13px;">กำลังดำเนินการ</div>
            <div style="color: #93c5fd; font-size: 24px; font-weight: bold; margin-top: 5px;">{{ $summary['working'] }} งาน</div>
        </div>

        <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #14532d; text-align: center;">
            <div style="color: #9ca3af; font-size: 13px;">ซ่อมเสร็จสิ้น</div>
            <div style="color: #86efac; font-size: 24px; font-weight: bold; margin-top: 5px;">{{ $summary['completed'] }} งาน</div>
        </div>

        <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #7f1d1d; text-align: center;">
            <div style="color: #9ca3af; font-size: 13px;">ยกเลิก</div>
            <div style="color: #fca5a5; font-size: 24px; font-weight: bold; margin-top: 5px;">{{ $summary['cancelled'] }} งาน</div>
        </div>
    </div>

    {{-- ตารางแสดงผลรายงาน --}}
    @if($repairs->isEmpty())
        <div style="text-align: center; padding: 35px; color: #9ca3af;">
            <p>ไม่พบข้อมูลงานซ่อมตามช่วงเวลาหรือสถานะที่ระบุ</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่แจ้ง</th>
                        <th>อุปกรณ์</th>
                        <th>สถานที่</th>
                        <th>ผู้แจ้ง</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>สถานะ</th>
                        <th>วันที่แจ้ง</th>
                        <th>วันที่เสร็จสิ้น</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repairs as $repair)
                        <tr>
                            <td style="font-weight: bold; color: #60a5fa;">{{ $repair->tracking_number }}</td>
                            <td>{{ $repair->equipment_name }}</td>
                            <td>{{ $repair->location }}</td>
                            <td>{{ $repair->reporter_name }}</td>
                            <td>{{ $repair->technician ? $repair->technician->name : 'ยังไม่มอบหมาย' }}</td>
                            <td>
                                <span class="status-badge {{ $repair->status_badge_class }}">
                                    {{ $repair->status }}
                                </span>
                            </td>
                            <td style="font-size: 13px; color: #9ca3af;">{{ $repair->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-size: 13px; color: #86efac;">
                                {{ $repair->completed_at ? $repair->completed_at->format('d/m/Y H:i') : '-' }}
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
