@extends('layouts.app')

@section('title', 'รายละเอียดงานซ่อม ' . $repair->tracking_number . ' | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

    {{-- ส่วนหัวของการ์ด --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="margin-bottom: 0;">ใบแจ้งซ่อมเลขที่ {{ $repair->tracking_number }}</h1>
                    <span class="status-badge {{ $repair->status_badge_class }}">
                        {{ $repair->status }}
                    </span>
                    <span class="priority-badge {{ $repair->priority_badge_class }}">
                        ความเร่งด่วน: {{ $repair->priority }}
                    </span>
                </div>
                <p class="subtitle" style="margin-top: 5px; margin-bottom: 0;">
                    วันที่แจ้ง: {{ $repair->created_at->format('d/m/Y H:i') }} น.
                </p>
            </div>

            <div>
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('repairs.index') }}" class="btn btn-secondary btn-sm">
                    &larr; ย้อนกลับ
                </a>
            </div>
        </div>

        {{-- ลำดับขั้นตอนการทำงาน (Workflow Progress Timeline) --}}
        <div style="margin-bottom: 30px; background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #1e3a8a;">
            <div style="font-size: 13px; color: #9ca3af; margin-bottom: 12px; font-weight: 600;">
                ขั้นตอนการดำเนินงาน:
            </div>
            <div style="display: flex; justify-content: space-between; position: relative; gap: 10px; flex-wrap: wrap;">
                @php
                    $steps = [
                        'รอดำเนินการ' => ['title' => 'ส่งแจ้งซ่อม', 'icon' => '1'],
                        'รับเรื่องแล้ว' => ['title' => 'มอบหมายช่าง', 'icon' => '2'],
                        'กำลังตรวจสอบ' => ['title' => 'ตรวจอาการ', 'icon' => '3'],
                        'กำลังซ่อม' => ['title' => 'กำลังซ่อม', 'icon' => '4'],
                        'ซ่อมเสร็จ' => ['title' => 'ซ่อมเสร็จสิ้น', 'icon' => '5'],
                    ];

                    $statusOrder = [
                        'รอดำเนินการ' => 1,
                        'รับเรื่องแล้ว' => 2,
                        'กำลังตรวจสอบ' => 3,
                        'กำลังซ่อม' => 4,
                        'ซ่อมเสร็จ' => 5,
                        'ยกเลิก' => -1,
                    ];

                    $currentOrder = $statusOrder[$repair->status] ?? 1;
                @endphp

                @if($repair->status === 'ยกเลิก')
                    <div style="color: #fca5a5; font-weight: 600;">
                        ❌ คำขอแจ้งซ่อมนี้ถูกยกเลิกแล้ว
                    </div>
                @else
                    @foreach($steps as $key => $step)
                        @php
                            $stepOrder = $statusOrder[$key];
                            $isActive = $currentOrder >= $stepOrder;
                            $isCurrent = $repair->status === $key;
                        @endphp
                        <div style="flex: 1; min-width: 110px; text-align: center;">
                            <div style="
                                width: 32px;
                                height: 32px;
                                border-radius: 50%;
                                margin: 0 auto 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: bold;
                                font-size: 13px;
                                background-color: {{ $isActive ? '#2563eb' : '#374151' }};
                                color: {{ $isActive ? '#ffffff' : '#9ca3af' }};
                                border: {{ $isCurrent ? '2px solid #60a5fa' : 'none' }};
                            ">
                                {{ $step['icon'] }}
                            </div>
                            <div style="font-size: 12px; color: {{ $isActive ? '#60a5fa' : '#9ca3af' }}; font-weight: {{ $isCurrent ? 'bold' : 'normal' }};">
                                {{ $step['title'] }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ข้อมูลรายละเอียดแบ่งเป็นส่วนๆ --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">

            {{-- 1. ข้อมูลผู้แจ้งและอุปกรณ์ --}}
            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: #60a5fa; border-bottom: 1px solid #1f2937; padding-bottom: 8px;">
                    👤 ข้อมูลผู้แจ้งซ่อม
                </h3>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>ชื่อผู้แจ้ง:</strong> {{ $repair->reporter_name }}</p>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>อีเมล:</strong> {{ $repair->user ? $repair->user->email : '-' }}</p>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>เบอร์ติดต่อ:</strong> {{ $repair->user && $repair->user->phone ? $repair->user->phone : '-' }}</p>
                <p style="font-size: 14px;"><strong>แผนก:</strong> {{ $repair->user && $repair->user->department ? $repair->user->department : '-' }}</p>
            </div>

            {{-- 2. ข้อมูลอุปกรณ์และสถานที่ --}}
            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: #60a5fa; border-bottom: 1px solid #1f2937; padding-bottom: 8px;">
                    ⚡ ข้อมูลอุปกรณ์ไฟฟ้า
                </h3>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>ชื่ออุปกรณ์:</strong> {{ $repair->equipment_name }}</p>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>ประเภท:</strong> {{ $repair->equipment_type }}</p>
                <p style="margin-bottom: 8px; font-size: 14px;"><strong>สถานที่ตั้ง:</strong> {{ $repair->location }}</p>
                <p style="font-size: 14px;">
                    <strong>รหัสครุภัณฑ์:</strong>
                    {{ $repair->equipment ? $repair->equipment->code : 'ไม่ได้ระบุรหัส' }}
                </p>
            </div>
        </div>

        {{-- 3. อาการเสียและรายละเอียดปัญหา --}}
        <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937; margin-bottom: 25px;">
            <h3 style="font-size: 16px; margin-bottom: 10px; color: #60a5fa;">
                📝 อาการเสีย / รายละเอียดปัญหาที่แจ้ง
            </h3>
            <div style="color: #ffffff; line-height: 1.6; white-space: pre-line; font-size: 14px;">
                {{ $repair->description }}
            </div>

            {{-- รูปภาพอุปกรณ์ที่แนบมา --}}
            @if($repair->image_path)
                <div style="margin-top: 20px; border-top: 1px solid #1f2937; padding-top: 15px;">
                    <div style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">
                        📷 รูปภาพอุปกรณ์ที่แนบ:
                    </div>
                    <div>
                        <a href="{{ asset('storage/' . $repair->image_path) }}" target="_blank">
                            <img
                                src="{{ asset('storage/' . $repair->image_path) }}"
                                alt="รูปภาพอุปกรณ์ชำรุด"
                                style="max-width: 100%; max-height: 350px; border-radius: 8px; border: 1px solid #3b82f6;"
                            >
                        </a>
                        <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">คลิกที่รูปเพื่อเปิดดูขนาดเต็ม</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- 4. ข้อมูลช่างและการดำเนินการซ่อม --}}
        <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1e3a8a; margin-bottom: 25px;">
            <h3 style="font-size: 16px; margin-bottom: 12px; color: #60a5fa; border-bottom: 1px solid #1f2937; padding-bottom: 8px;">
                🔧 ข้อมูลช่างผู้รับผิดชอบและการดำเนินงาน
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <p style="font-size: 14px;">
                    <strong>ช่างผู้รับผิดชอบ:</strong>
                    @if($repair->technician)
                        <span style="color: #93c5fd; font-weight: 600;">{{ $repair->technician->name }}</span>
                        ({{ $repair->technician->phone ?: 'ไม่มีเบอร์โทร' }})
                    @else
                        <span style="color: #fbbf24;">ยังไม่ได้รับการมอบหมายช่าง</span>
                    @endif
                </p>

                <p style="font-size: 14px;">
                    <strong>วันที่มอบหมาย:</strong>
                    {{ $repair->assigned_at ? $repair->assigned_at->format('d/m/Y H:i') . ' น.' : '-' }}
                </p>

                <p style="font-size: 14px;">
                    <strong>วันที่ซ่อมเสร็จสิ้น:</strong>
                    {{ $repair->completed_at ? $repair->completed_at->format('d/m/Y H:i') . ' น.' : '-' }}
                </p>
            </div>

            @if($repair->admin_notes)
                <div style="margin-bottom: 12px; font-size: 14px; background: #111827; padding: 10px; border-radius: 6px;">
                    <strong style="color: #93c5fd;">คำสั่งการ/หมายเหตุแอดมิน:</strong>
                    <div style="color: #dbeafe; margin-top: 4px;">{{ $repair->admin_notes }}</div>
                </div>
            @endif

            @if($repair->inspection_result)
                <div style="margin-bottom: 12px; font-size: 14px; background: #111827; padding: 10px; border-radius: 6px;">
                    <strong style="color: #fbbf24;">ผลการตรวจสอบของช่าง:</strong>
                    <div style="color: #dbeafe; margin-top: 4px;">{{ $repair->inspection_result }}</div>
                </div>
            @endif

            @if($repair->parts_used)
                <div style="margin-bottom: 12px; font-size: 14px; background: #111827; padding: 10px; border-radius: 6px;">
                    <strong style="color: #60a5fa;">อะไหล่ที่ใช้/เปลี่ยน:</strong>
                    <div style="color: #dbeafe; margin-top: 4px;">{{ $repair->parts_used }}</div>
                </div>
            @endif

            @if($repair->repair_detail)
                <div style="font-size: 14px; background: #111827; padding: 10px; border-radius: 6px;">
                    <strong style="color: #86efac;">ผลการซ่อม/รายละเอียดการปฏิบัติงาน:</strong>
                    <div style="color: #dbeafe; margin-top: 4px;">{{ $repair->repair_detail }}</div>
                </div>
            @endif

            @if(!$repair->inspection_result && !$repair->repair_detail)
                <p style="color: #9ca3af; font-size: 13px; font-style: italic;">
                    * อยู่ระหว่างรอช่างเข้าตรวจสอบและบันทึกผลการดำเนินงาน
                </p>
            @endif
        </div>

        {{-- ปุ่มลัดสำหรับการจัดการงานตามบทบาท --}}
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.repairs.show', $repair) }}" class="btn btn-primary">
                    ⚙️ จัดการ/มอบหมายงานในฐานะแอดมิน
                </a>
            @endif

            @if(Auth::user()->isTechnician() && $repair->technician_id === Auth::id())
                <a href="{{ route('technician.repairs.show', $repair) }}" class="btn btn-success">
                    🛠️ บันทึกผลการซ่อมในฐานะช่าง
                </a>
            @endif

            <a href="{{ route('repairs.status') }}" class="btn btn-secondary">
                📊 ตรวจสอบสถานะงานอื่นๆ
            </a>
        </div>

    </div>

</div>
@endsection
