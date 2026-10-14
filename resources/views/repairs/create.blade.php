@extends('layouts.app')

@section('title', 'แจ้งซ่อมเครื่องใช้ไฟฟ้า | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท')

@section('content')
{{-- ส่วนคอนเทนเนอร์ของฟอร์มแจ้งซ่อม จัดกึ่งกลางขนาด 750px ตามดีไซน์เดิม --}}
<div style="max-width: 750px; margin: 0 auto;">

    <div class="card">
        {{-- ส่วนหัวของฟอร์ม --}}
        <div class="card-header">
            <div>
                <h1>แจ้งซ่อมเครื่องใช้ไฟฟ้า</h1>
                <p class="subtitle" style="margin-bottom: 0;">กรุณากรอกข้อมูลอุปกรณ์และแนบรูปภาพปัญหาที่พบ</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                &larr; กลับหน้าหลัก
            </a>
        </div>

        {{-- แสดงข้อผิดพลาดจาก Validation หากมี --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <div>
                    <strong>เกิดข้อผิดพลาด:</strong>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ฟอร์มแจ้งซ่อม ส่งข้อมูลแบบ POST และรองรับการอัปโหลดไฟล์ (multipart/form-data) --}}
        <form action="{{ route('repairs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 1. ชื่อผู้แจ้งซ่อม (ดึงค่าเริ่มต้นจากผู้ใช้ที่ล็อกอินอยู่) --}}
            <div class="form-group">
                <label for="name">ชื่อผู้แจ้ง <span style="color: #ef4444;">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', Auth::user()->name) }}"
                    placeholder="กรอกชื่อ-นามสกุล ผู้แจ้ง"
                    required
                >
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- ตัวเลือกเลือกอุปกรณ์จากระบบครุภัณฑ์ (ถ้ามี) --}}
            @if(isset($equipmentList) && $equipmentList->isNotEmpty())
                <div class="form-group">
                    <label for="equipment_id">เลือกอุปกรณ์จากระบบ (หรือกรอกเองด้านล่าง)</label>
                    <select id="equipment_id" name="equipment_id" onchange="autoFillEquipment(this)">
                        <option value="">-- ไม่ระบุ / เป็นอุปกรณ์ใหม่ที่ไม่มีในระบบ --</option>
                        @foreach($equipmentList as $item)
                            <option
                                value="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-type="{{ $item->type }}"
                                data-location="{{ $item->location }}"
                                {{ old('equipment_id') == $item->id ? 'selected' : '' }}
                            >
                                [{{ $item->code }}] {{ $item->name }} - {{ $item->location }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- 2. ชื่ออุปกรณ์ --}}
            <div class="form-group">
                <label for="equipment">ชื่ออุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <input
                    type="text"
                    id="equipment"
                    name="equipment"
                    value="{{ old('equipment') }}"
                    placeholder="เช่น เครื่องปรับอากาศ Daikin, คอมพิวเตอร์ Dell"
                    required
                >
                @error('equipment')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3. ประเภทอุปกรณ์ --}}
            <div class="form-group">
                <label for="type">ประเภทอุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <select id="type" name="type" required>
                    <option value="">-- เลือกประเภทอุปกรณ์ --</option>
                    <option value="เครื่องปรับอากาศ" {{ old('type') == 'เครื่องปรับอากาศ' ? 'selected' : '' }}>เครื่องปรับอากาศ</option>
                    <option value="คอมพิวเตอร์" {{ old('type') == 'คอมพิวเตอร์' ? 'selected' : '' }}>คอมพิวเตอร์</option>
                    <option value="เครื่องพิมพ์" {{ old('type') == 'เครื่องพิมพ์' ? 'selected' : '' }}>เครื่องพิมพ์</option>
                    <option value="ตู้เย็น" {{ old('type') == 'ตู้เย็น' ? 'selected' : '' }}>ตู้เย็น</option>
                    <option value="พัดลม" {{ old('type') == 'พัดลม' ? 'selected' : '' }}>พัดลม</option>
                    <option value="อุปกรณ์ไฟฟ้าอื่นๆ" {{ old('type') == 'อุปกรณ์ไฟฟ้าอื่นๆ' ? 'selected' : '' }}>อุปกรณ์ไฟฟ้าอื่นๆ</option>
                </select>
                @error('type')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. สถานที่ตั้งอุปกรณ์ --}}
            <div class="form-group">
                <label for="location">สถานที่ตั้งอุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    value="{{ old('location') }}"
                    placeholder="เช่น ห้องสำนักงาน ชั้น 2, ห้องประชุม 1"
                    required
                >
                @error('location')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 5. ระดับความเร่งด่วน --}}
            <div class="form-group">
                <label for="priority">ระดับความเร่งด่วน <span style="color: #ef4444;">*</span></label>
                <select id="priority" name="priority" required>
                    <option value="">-- เลือกระดับความเร่งด่วน --</option>
                    <option value="ต่ำ" {{ old('priority') == 'ต่ำ' ? 'selected' : '' }}>ต่ำ (ไม่ส่งผลกระทบต่องานทันที)</option>
                    <option value="ปานกลาง" {{ old('priority', 'ปานกลาง') == 'ปานกลาง' ? 'selected' : '' }}>ปานกลาง (ใช้งานได้บางส่วน)</option>
                    <option value="สูง" {{ old('priority') == 'สูง' ? 'selected' : '' }}>สูง (ส่งผลต่องานทั่วไป)</option>
                    <option value="เร่งด่วน" {{ old('priority') == 'เร่งด่วน' ? 'selected' : '' }}>เร่งด่วน (หยุดชะงักการทำงาน/อาจเกิดอันตราย)</option>
                </select>
                @error('priority')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 6. อาการเสีย / รายละเอียดปัญหา --}}
            <div class="form-group">
                <label for="description">อาการเสีย / รายละเอียดปัญหา <span style="color: #ef4444;">*</span></label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="อธิบายอาการเสีย เช่น เปิดไม่ติด มีเสียงดัง ไฟไม่เข้า มีกลิ่นไหม้ ฯลฯ"
                    required
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 7. แนบรูปภาพอุปกรณ์หรือจุดที่ชำรุด --}}
            <div class="form-group">
                <label for="image">รูปภาพอุปกรณ์หรือปัญหา (ถ้ามี)</label>
                <div style="border: 2px dashed #3b82f6; border-radius: 8px; padding: 25px; text-align: center; background: #0f172a;">
                    <p style="color: #9ca3af; margin-bottom: 12px; font-size: 14px;">
                        แนบรูปภาพอุปกรณ์ที่ชำรุด (รองรับไฟล์ JPG, PNG, WEBP ขนาดไม่เกิน 5 MB)
                    </p>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        onchange="previewImage(event)"
                        style="max-width: 320px;"
                    >
                    <div id="imagePreviewContainer" style="margin-top: 15px; display: none;">
                        <img id="imagePreview" src="#" alt="ตัวอย่างรูปภาพ" style="max-height: 200px; border-radius: 6px; border: 1px solid #3b82f6;">
                    </div>
                </div>
                @error('image')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- ปุ่มกดส่งแบบฟอร์ม --}}
            <div style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 13px; font-size: 16px;">
                    🚀 ส่งแจ้งซ่อม
                </button>
            </div>

        </form>

    </div>

</div>
@endsection

@section('scripts')
{{-- สคริปต์ช่วยอำนวยความสะดวกในการกรอกข้อมูลอัตโนมัติและพรีวิวรูปภาพ --}}
<script>
    // เติมข้อมูลอัตโนมัติเมื่อเลือกอุปกรณ์ที่มีอยู่ในระบบ
    function autoFillEquipment(select) {
        var option = select.options[select.selectedIndex];
        if (option.value) {
            document.getElementById('equipment').value = option.getAttribute('data-name') || '';
            document.getElementById('type').value = option.getAttribute('data-type') || '';
            document.getElementById('location').value = option.getAttribute('data-location') || '';
        }
    }

    // แสดงรูปภาพตัวอย่างก่อนส่งฟอร์ม
    function previewImage(event) {
        var input = event.target;
        var preview = document.getElementById('imagePreview');
        var container = document.getElementById('imagePreviewContainer');

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            container.style.display = 'none';
        }
    }
</script>
@endsection