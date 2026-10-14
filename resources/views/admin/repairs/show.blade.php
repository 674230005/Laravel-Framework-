@extends('layouts.app')

@section('title', 'จัดการและมอบหมายงาน ' . $repair->tracking_number . ' (Admin)')

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    {{-- การ์ดรายละเอียดงานแจ้งซ่อม --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="margin-bottom: 0;">ใบแจ้งซ่อมเลขที่ {{ $repair->tracking_number }}</h1>
                    <span class="status-badge {{ $repair->status_badge_class }}">{{ $repair->status }}</span>
                    <span class="priority-badge {{ $repair->priority_badge_class }}">ความเร่งด่วน: {{ $repair->priority }}</span>
                </div>
                <p class="subtitle" style="margin-top: 5px; margin-bottom: 0;">
                    ผู้แจ้ง: {{ $repair->reporter_name }} | วันที่แจ้ง: {{ $repair->created_at->format('d/m/Y H:i') }} น.
                </p>
            </div>
            <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary btn-sm">
                &larr; กลับรายการงาน
            </a>
        </div>

        {{-- ฟอร์มการมอบหมายช่างและปรับปรุงสถานะ (เฉพาะแอดมิน) --}}
        <div style="background: #0f172a; border: 2px solid #2563eb; border-radius: 8px; padding: 22px; margin-bottom: 25px;">
            <h2 style="font-size: 18px; color: #60a5fa; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span>👨‍🔧</span> จัดการมอบหมายงานและสถานะ (แอดมิน)
            </h2>

            <form action="{{ route('admin.repairs.assign', $repair) }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 15px;">
                    {{-- เลือกช่างผู้รับผิดชอบ --}}
                    <div class="form-group">
                        <label for="technician_id">มอบหมายให้ช่างผู้รับผิดชอบ</label>
                        <select id="technician_id" name="technician_id">
                            <option value="">-- ยังไม่มอบหมาย / ยกเลิกการมอบหมาย --</option>
                            @foreach($technicians as $tech)
                                <option
                                    value="{{ $tech->id }}"
                                    {{ old('technician_id', $repair->technician_id) == $tech->id ? 'selected' : '' }}
                                >
                                    {{ $tech->name }} ({{ $tech->phone ?: 'ไม่มีเบอร์' }}) - {{ $tech->department }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ปรับปรุงสถานะงาน --}}
                    <div class="form-group">
                        <label for="status">สถานะงานซ่อม</label>
                        <select id="status" name="status" required>
                            <option value="รอดำเนินการ" {{ old('status', $repair->status) == 'รอดำเนินการ' ? 'selected' : '' }}>รอดำเนินการ (ยังไม่มอบหมาย)</option>
                            <option value="รับเรื่องแล้ว" {{ old('status', $repair->status) == 'รับเรื่องแล้ว' ? 'selected' : '' }}>รับเรื่องแล้ว (มอบหมายช่างแล้ว)</option>
                            <option value="กำลังตรวจสอบ" {{ old('status', $repair->status) == 'กำลังตรวจสอบ' ? 'selected' : '' }}>กำลังตรวจสอบ (ช่างลงพื้นที่ตรวจ)</option>
                            <option value="กำลังซ่อม" {{ old('status', $repair->status) == 'กำลังซ่อม' ? 'selected' : '' }}>กำลังซ่อม (ช่างกำลังแก้ไข/เปลี่ยนอะไหล่)</option>
                            <option value="ซ่อมเสร็จ" {{ old('status', $repair->status) == 'ซ่อมเสร็จ' ? 'selected' : '' }}>ซ่อมเสร็จ (ปิดงานแล้ว)</option>
                            <option value="ยกเลิก" {{ old('status', $repair->status) == 'ยกเลิก' ? 'selected' : '' }}>ยกเลิก (ยกเลิกคำขอนี้)</option>
                        </select>
                    </div>
                </div>

                {{-- หมายเหตุ/คำสั่งการจากแอดมิน --}}
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="admin_notes">คำสั่งการ / หมายเหตุจากแอดมินถึงช่าง</label>
                    <textarea
                        id="admin_notes"
                        name="admin_notes"
                        placeholder="เช่น มอบหมายให้ช่างสมชายตรวจเช็กชุดมอเตอร์ด่วน หรือ อนุมัติการเปลี่ยนอะไหล่"
                        style="min-height: 80px;"
                    >{{ old('admin_notes', $repair->admin_notes) }}</textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 22px;">
                        💾 บันทึกการมอบหมายและสถานะ
                    </button>
                </div>
            </form>
        </div>

        {{-- รายละเอียดอุปกรณ์และปัญหา --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">⚡ ข้อมูลอุปกรณ์</h3>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>ชื่อ:</strong> {{ $repair->equipment_name }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>ประเภท:</strong> {{ $repair->equipment_type }}</p>
                <p style="font-size: 14px;"><strong>สถานที่:</strong> {{ $repair->location }}</p>
            </div>

            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">👤 ข้อมูลผู้แจ้ง</h3>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>ชื่อ:</strong> {{ $repair->reporter_name }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>เบอร์โทร:</strong> {{ $repair->user ? $repair->user->phone : '-' }}</p>
                <p style="font-size: 14px;"><strong>แผนก:</strong> {{ $repair->user ? $repair->user->department : '-' }}</p>
            </div>
        </div>

        {{-- ปัญหาที่แจ้งและรูปภาพ --}}
        <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937; margin-bottom: 25px;">
            <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">📝 รายละเอียดอาการเสียที่แจ้ง</h3>
            <p style="line-height: 1.6; font-size: 14px; white-space: pre-line;">{{ $repair->description }}</p>

            @if($repair->image_path)
                <div style="margin-top: 15px; border-top: 1px solid #1f2937; padding-top: 15px;">
                    <div style="color: #9ca3af; font-size: 13px; margin-bottom: 6px;">รูปภาพที่แนบ:</div>
                    <a href="{{ asset('storage/' . $repair->image_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $repair->image_path) }}" alt="ภาพอุปกรณ์" style="max-height: 250px; border-radius: 6px; border: 1px solid #3b82f6;">
                    </a>
                </div>
            @endif
        </div>

        {{-- ข้อมูลผลการซ่อมของช่าง --}}
        <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1e3a8a;">
            <h3 style="font-size: 15px; margin-bottom: 12px; color: #60a5fa;">🔧 บันทึกผลการซ่อมจากช่าง</h3>
            <p style="font-size: 14px; margin-bottom: 8px;">
                <strong>ผลการตรวจสอบของช่าง:</strong>
                {{ $repair->inspection_result ?: 'ยังไม่มีข้อมูล' }}
            </p>
            <p style="font-size: 14px; margin-bottom: 8px;">
                <strong>อะไหล่ที่ใช้:</strong>
                {{ $repair->parts_used ?: 'ยังไม่มีข้อมูล' }}
            </p>
            <p style="font-size: 14px; margin-bottom: 8px;">
                <strong>ผลการซ่อม/วิธีแก้:</strong>
                {{ $repair->repair_detail ?: 'ยังไม่มีข้อมูล' }}
            </p>
            <p style="font-size: 14px; color: #9ca3af;">
                <strong>เวลาเสร็จสิ้น:</strong>
                {{ $repair->completed_at ? $repair->completed_at->format('d/m/Y H:i') . ' น.' : 'ยังไม่เสร็จสิ้น' }}
            </p>
        </div>

    </div>

</div>
@endsection
