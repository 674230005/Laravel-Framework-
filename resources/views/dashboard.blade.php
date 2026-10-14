@extends('layouts.app')

@section('title', 'หน้าหลัก | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท')

@section('content')
{{-- ส่วนหัวของหน้า Dashboard --}}
<div style="margin-bottom: 25px;">
    <h1>หน้าหลัก (Dashboard)</h1>
    <p class="subtitle">
        ยินดีต้อนรับคุณ {{ $user->name }} | สิทธิ์การใช้งาน: <span style="color: #60a5fa;">{{ $user->role_name_thai }}</span>
    </p>
</div>

{{-- 1. การ์ดสรุปสถิติ (Statistics Cards) แสดงตามบทบาท --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 30px;">
    @if($user->isAdmin())
        {{-- สถิติสำหรับ Admin --}}
        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">📋</div>
            <div style="color: #9ca3af; font-size: 13px;">งานแจ้งซ่อมทั้งหมด</div>
            <div style="color: #60a5fa; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['total_repairs'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">⏳</div>
            <div style="color: #9ca3af; font-size: 13px;">รอดำเนินการ / รอมอบหมาย</div>
            <div style="color: #fbbf24; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['pending'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">🔧</div>
            <div style="color: #9ca3af; font-size: 13px;">กำลังดำเนินการซ่อม</div>
            <div style="color: #93c5fd; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['working'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">✅</div>
            <div style="color: #9ca3af; font-size: 13px;">ซ่อมเสร็จสิ้นแล้ว</div>
            <div style="color: #86efac; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['completed'] }} รายการ</div>
        </div>
    @elseif($user->isTechnician())
        {{-- สถิติสำหรับช่างซ่อม (Technician) --}}
        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">🛠️</div>
            <div style="color: #9ca3af; font-size: 13px;">งานที่ได้รับมอบหมายทั้งหมด</div>
            <div style="color: #60a5fa; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['assigned_total'] }} งาน</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">🔍</div>
            <div style="color: #9ca3af; font-size: 13px;">รอตรวจสอบ / รับเรื่อง</div>
            <div style="color: #fbbf24; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['assigned_pending'] }} งาน</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">⚡</div>
            <div style="color: #9ca3af; font-size: 13px;">กำลังดำเนินการซ่อม</div>
            <div style="color: #93c5fd; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['assigned_in_progress'] }} งาน</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">🎉</div>
            <div style="color: #9ca3af; font-size: 13px;">ซ่อมเสร็จเรียบร้อยแล้ว</div>
            <div style="color: #86efac; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['assigned_completed'] }} งาน</div>
        </div>
    @else
        {{-- สถิติสำหรับพนักงานทั่วไป (User) --}}
        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">📝</div>
            <div style="color: #9ca3af; font-size: 13px;">รายการที่ฉันแจ้งทั้งหมด</div>
            <div style="color: #60a5fa; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['my_total'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">⏳</div>
            <div style="color: #9ca3af; font-size: 13px;">รอดำเนินการ</div>
            <div style="color: #fbbf24; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['my_pending'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">🔧</div>
            <div style="color: #9ca3af; font-size: 13px;">กำลังซ่อมบำรุง</div>
            <div style="color: #93c5fd; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['my_working'] }} รายการ</div>
        </div>

        <div class="card" style="margin: 0; padding: 20px;">
            <div style="font-size: 28px; margin-bottom: 8px;">✅</div>
            <div style="color: #9ca3af; font-size: 13px;">ซ่อมเสร็จสิ้น</div>
            <div style="color: #86efac; font-size: 26px; font-weight: bold; margin-top: 5px;">{{ $stats['my_completed'] }} รายการ</div>
        </div>
    @endif
</div>

{{-- 2. การ์ดเมนูการทำงานหลัก (Action Cards ตามรูปแบบหน้าเดิม) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px;">
    {{-- การ์ดแจ้งซ่อม --}}
    <div class="card" style="margin: 0;">
        <div style="font-size: 35px; margin-bottom: 12px;">📝</div>
        <h2 style="margin-bottom: 8px;">แจ้งซ่อมเครื่องใช้ไฟฟ้า</h2>
        <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 18px; font-size: 14px;">
            แจ้งปัญหาเครื่องใช้ไฟฟ้าชำรุด ระบุสถานที่ ความเร่งด่วน พร้อมแนบรูปภาพอุปกรณ์
        </p>
        <a href="{{ route('repairs.create') }}" class="btn btn-primary">
            + ส่งแจ้งซ่อมใหม่
        </a>
    </div>

    {{-- การ์ดงานซ่อม --}}
    <div class="card" style="margin: 0;">
        <div style="font-size: 35px; margin-bottom: 12px;">🔧</div>
        <h2 style="margin-bottom: 8px;">รายการแจ้งซ่อม</h2>
        <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 18px; font-size: 14px;">
            ตรวจสอบรายการแจ้งซ่อม ค้นหาตามรหัสหรือชื่ออุปกรณ์ และดูประวัติย้อนหลัง
        </p>
        <a href="{{ route('repairs.index') }}" class="btn btn-primary">
            ดูรายการงานซ่อม
        </a>
    </div>

    {{-- การ์ดติดตามสถานะ --}}
    <div class="card" style="margin: 0;">
        <div style="font-size: 35px; margin-bottom: 12px;">📊</div>
        <h2 style="margin-bottom: 8px;">ติดตามสถานะงาน</h2>
        <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 18px; font-size: 14px;">
            ตรวจสอบขั้นตอนการทำงาน ดูช่างผู้รับผิดชอบ และผลการตรวจซ่อมแบบเรียลไทม์
        </p>
        <a href="{{ route('repairs.status') }}" class="btn btn-primary">
            ติดตามสถานะ
        </a>
    </div>
</div>

{{-- 3. ตารางรายการงานซ่อมล่าสุด (Recent Repairs Table) --}}
<div class="card">
    <div class="card-header">
        <h2>
            @if($user->isAdmin())
                📋 รายการแจ้งซ่อมล่าสุดในระบบ
            @elseif($user->isTechnician())
                🛠️ งานที่ได้รับมอบหมายล่าสุด
            @else
                📝 ประวัติการแจ้งซ่อมล่าสุดของฉัน
            @endif
        </h2>
        <a href="{{ $user->isTechnician() ? route('technician.repairs.index') : route('repairs.index') }}" class="btn btn-secondary btn-sm">
            ดูทั้งหมด &rarr;
        </a>
    </div>

    @if($recentRepairs->isEmpty())
        <p style="color: #9ca3af; text-align: center; padding: 25px 0;">
            ยังไม่มีรายการงานซ่อมในระบบ
        </p>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่แจ้งซ่อม</th>
                        <th>อุปกรณ์</th>
                        <th>สถานที่</th>
                        <th>ผู้แจ้ง</th>
                        <th>ความเร่งด่วน</th>
                        <th>สถานะ</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRepairs as $repair)
                        <tr>
                            <td style="font-weight: 600; color: #60a5fa;">{{ $repair->tracking_number }}</td>
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
                                {{ $repair->technician ? $repair->technician->name : 'ยังไม่มอบหมาย' }}
                            </td>
                            <td style="text-align: center;">
                                @if($user->isAdmin())
                                    <a href="{{ route('admin.repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                        จัดการงาน
                                    </a>
                                @elseif($user->isTechnician())
                                    <a href="{{ route('technician.repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                        บันทึกงาน
                                    </a>
                                @else
                                    <a href="{{ route('repairs.show', $repair) }}" class="btn btn-primary btn-sm">
                                        ดูรายละเอียด
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection