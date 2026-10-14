@extends('layouts.app')

@section('title', 'บันทึกผลการซ่อม ' . $repair->tracking_number . ' (ช่างซ่อม)')

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    <div class="card">
        <div class="card-header">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="margin-bottom: 0;">งานซ่อมเลขที่ {{ $repair->tracking_number }}</h1>
                    <span class="status-badge {{ $repair->status_badge_class }}">{{ $repair->status }}</span>
                    <span class="priority-badge {{ $repair->priority_badge_class }}">ความเร่งด่วน: {{ $repair->priority }}</span>
                </div>
                <p class="subtitle" style="margin-top: 5px; margin-bottom: 0;">
                    อุปกรณ์: {{ $repair->equipment_name }} | สถานที่: {{ $repair->location }}
                </p>
            </div>
            <a href="{{ route('technician.repairs.index') }}" class="btn btn-secondary btn-sm">
                &larr; กลับรายการงานช่าง
            </a>
        </div>

        {{-- ฟอร์มการอัปเดตและบันทึกผลการซ่อมของช่าง --}}
        <div style="background: #0f172a; border: 2px solid #16a34a; border-radius: 8px; padding: 22px; margin-bottom: 25px;">
            <h2 style="font-size: 18px; color: #86efac; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <span>🛠️</span> บันทึกการดำเนินงานและผลการซ่อม (ช่างซ่อม)
            </h2>

            <form action="{{ route('technician.repairs.update', $repair) }}" method="POST">
                @csrf

                {{-- สถานะการดำเนินงาน --}}
                <div class="form-group">
                    <label for="status">สถานะขั้นตอนการทำงาน <span style="color: #ef4444;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="รับเรื่องแล้ว" {{ old('status', $repair->status) == 'รับเรื่องแล้ว' ? 'selected' : '' }}>
                            1. รับเรื่องแล้ว (รับทราบงานแล้ว)
                        </option>
                        <option value="กำลังตรวจสอบ" {{ old('status', $repair->status) == 'กำลังตรวจสอบ' ? 'selected' : '' }}>
                            2. กำลังตรวจสอบ (กำลังลงพื้นที่ตรวจเช็กอาการ)
                        </option>
                        <option value="กำลังซ่อม" {{ old('status', $repair->status) == 'กำลังซ่อม' ? 'selected' : '' }}>
                            3. กำลังซ่อม (กำลังแก้ไข / รอเปลี่ยนอะไหล่)
                        </option>
                        <option value="ซ่อมเสร็จ" {{ old('status', $repair->status) == 'ซ่อมเสร็จ' ? 'selected' : '' }}>
                            4. ซ่อมเสร็จ (แก้ไขเรียบร้อย ทดสอบผ่าน ปิดงาน)
                        </option>
                        <option value="ยกเลิก" {{ old('status', $repair->status) == 'ยกเลิก' ? 'selected' : '' }}>
                            5. ยกเลิก (ไม่สามารถซ่อมได้ / ซ้ำซ้อน)
                        </option>
                    </select>
                </div>

                {{-- ผลการตรวจสอบ / อาการที่ตรวจพบจริง --}}
                <div class="form-group">
                    <label for="inspection_result">ผลการตรวจสอบอาการเสียจริง / สาเหตุ</label>
                    <textarea
                        id="inspection_result"
                        name="inspection_result"
                        placeholder="ระบุสิ่งที่ตรวจพบจริง เช่น ตรวจพบสายไฟขาด, มอเตอร์ไหม้, น้ำยาแอร์รั่ว ฯลฯ"
                        style="min-height: 80px;"
                    >{{ old('inspection_result', $repair->inspection_result) }}</textarea>
                </div>

                {{-- อะไหล่ที่ใช้ในการซ่อม --}}
                <div class="form-group">
                    <label for="parts_used">อะไหล่และอุปกรณ์ที่เปลี่ยน (ถ้ามี)</label>
                    <textarea
                        id="parts_used"
                        name="parts_used"
                        placeholder="ระบุรายการอะไหล่ เช่น ฟิวส์ 10A 1 ตัว, คาปาซิเตอร์ 45uF 1 ชิ้น"
                        style="min-height: 70px;"
                    >{{ old('parts_used', $repair->parts_used) }}</textarea>
                </div>

                {{-- รายละเอียดผลการซ่อม / วิธีการแก้ไข --}}
                <div class="form-group">
                    <label for="repair_detail">ผลการดำเนินงาน / วิธีการซ่อมบำรุง / การทดสอบระบบ</label>
                    <textarea
                        id="repair_detail"
                        name="repair_detail"
                        placeholder="เช่น ดำเนินการต่อสายไฟและเปลี่ยนฟิวส์ใหม่ ทดสอบเปิดเครื่องใช้งาน 1 ชั่วโมง ทำงานได้เป็นปกติ"
                        style="min-height: 90px;"
                    >{{ old('repair_detail', $repair->repair_detail) }}</textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 20px;">
                    <div style="font-size: 13px; color: #9ca3af;">
                        * หากเลือกสถานะเป็น "ซ่อมเสร็จ" ระบบจะบันทึกเวลาปิดงานอัตโนมัติ
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success" style="padding: 10px 22px;">
                            💾 บันทึกความคืบหน้า / ปิดงาน
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- คำสั่งการหรือหมายเหตุจากแอดมิน --}}
        @if($repair->admin_notes)
            <div style="background: #1e3a8a; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #60a5fa;">
                <strong style="color: #ffffff; display: block; margin-bottom: 4px;">📢 คำสั่งการจากผู้ดูแลระบบ:</strong>
                <div style="color: #dbeafe; font-size: 14px;">{{ $repair->admin_notes }}</div>
            </div>
        @endif

        {{-- รายละเอียดปัญหาที่ผู้ใช้แจ้งและรูปภาพ --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">⚡ ข้อมูลอุปกรณ์และสถานที่</h3>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>อุปกรณ์:</strong> {{ $repair->equipment_name }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>ประเภท:</strong> {{ $repair->equipment_type }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>สถานที่:</strong> {{ $repair->location }}</p>
                <p style="font-size: 14px;"><strong>ความเร่งด่วน:</strong> {{ $repair->priority }}</p>
            </div>

            <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
                <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">👤 ข้อมูลผู้แจ้งซ่อม</h3>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>ชื่อผู้แจ้ง:</strong> {{ $repair->reporter_name }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>เบอร์โทรติดต่อ:</strong> {{ $repair->user && $repair->user->phone ? $repair->user->phone : '-' }}</p>
                <p style="font-size: 14px; margin-bottom: 6px;"><strong>แผนก:</strong> {{ $repair->user && $repair->user->department ? $repair->user->department : '-' }}</p>
                <p style="font-size: 14px;"><strong>วันที่แจ้ง:</strong> {{ $repair->created_at->format('d/m/Y H:i') }} น.</p>
            </div>
        </div>

        <div style="background: #0f172a; padding: 18px; border-radius: 8px; border: 1px solid #1f2937;">
            <h3 style="font-size: 15px; margin-bottom: 10px; color: #60a5fa;">📝 อาการเสียที่ได้รับแจ้ง</h3>
            <p style="font-size: 14px; line-height: 1.6; white-space: pre-line;">{{ $repair->description }}</p>

            @if($repair->image_path)
                <div style="margin-top: 15px; border-top: 1px solid #1f2937; padding-top: 15px;">
                    <div style="color: #9ca3af; font-size: 13px; margin-bottom: 8px;">รูปภาพอุปกรณ์ชำรุด:</div>
                    <a href="{{ asset('storage/' . $repair->image_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $repair->image_path) }}" alt="รูปภาพอุปกรณ์" style="max-height: 250px; border-radius: 6px; border: 1px solid #3b82f6;">
                    </a>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
