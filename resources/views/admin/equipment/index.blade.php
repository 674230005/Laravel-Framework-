@extends('layouts.app')

@section('title', 'จัดการข้อมูลอุปกรณ์ไฟฟ้า (Admin) | ระบบแจ้งซ่อม')

@section('content')
{{-- ฟอร์มเพิ่มอุปกรณ์ใหม่ --}}
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <div>
            <h1>📦 จัดการข้อมูลเครื่องใช้ไฟฟ้าในระบบ</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                บันทึกและจัดการรายการอุปกรณ์ไฟฟ้า เครื่องปรับอากาศ คอมพิวเตอร์ และครุภัณฑ์ในบริษัท
            </p>
        </div>
    </div>

    {{-- ฟอร์มบันทึกข้อมูลเครื่องใช้ไฟฟ้าใหม่ --}}
    <form action="{{ route('admin.equipment.store') }}" method="POST" style="background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #1e3a8a;">
        @csrf
        <h3 style="font-size: 16px; margin-bottom: 15px; color: #60a5fa;">+ เพิ่มอุปกรณ์ไฟฟ้าใหม่เข้าสู่ระบบ</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="code">รหัสอุปกรณ์ / ครุภัณฑ์ <span style="color: #ef4444;">*</span></label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" placeholder="เช่น EQ-AC-006" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">ชื่ออุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="เช่น แอร์ Daikin ห้องประชุม 2" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="type">ประเภทอุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <select id="type" name="type" required>
                    <option value="">-- เลือกประเภท --</option>
                    <option value="เครื่องปรับอากาศ">เครื่องปรับอากาศ</option>
                    <option value="คอมพิวเตอร์">คอมพิวเตอร์</option>
                    <option value="เครื่องพิมพ์">เครื่องพิมพ์</option>
                    <option value="ตู้เย็น">ตู้เย็น</option>
                    <option value="พัดลม">พัดลม</option>
                    <option value="อุปกรณ์ไฟฟ้าอื่นๆ">อุปกรณ์ไฟฟ้าอื่นๆ</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="location">สถานที่ติดตั้ง <span style="color: #ef4444;">*</span></label>
                <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="เช่น อาคาร A ชั้น 2" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="status">สถานะอุปกรณ์ <span style="color: #ef4444;">*</span></label>
                <select id="status" name="status" required>
                    <option value="ปกติ">ปกติ (พร้อมใช้งาน)</option>
                    <option value="ชำรุด">ชำรุด (รอซ่อม)</option>
                    <option value="กำลังซ่อม">กำลังซ่อม</option>
                    <option value="ปลดระวาง">ปลดระวาง</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px; margin-bottom: 15px;">
            <label for="description">รายละเอียดเพิ่มเติม</label>
            <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="เช่น ยี่ห้อ รุ่น สเปก หรือข้อมูลสำคัญ">
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary">
                💾 บันทึกอุปกรณ์
            </button>
        </div>
    </form>
</div>

{{-- ตารางรายการอุปกรณ์ไฟฟ้า --}}
<div class="card">
    <div class="card-header">
        <h2>📋 รายการอุปกรณ์ไฟฟ้าทั้งหมด ({{ $equipmentList->total() }} รายการ)</h2>

        {{-- ค้นหาอุปกรณ์ --}}
        <form action="{{ route('admin.equipment.index') }}" method="GET" style="display: flex; gap: 8px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 ค้นหารหัส, ชื่อ, สถานที่..."
                style="width: 250px; padding: 7px 12px;"
            >
            <button type="submit" class="btn btn-secondary btn-sm">ค้นหา</button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary btn-sm">รีเซ็ต</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>รหัสอุปกรณ์</th>
                    <th>ชื่ออุปกรณ์</th>
                    <th>ประเภท</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th>ประวัติแจ้งซ่อม</th>
                    <th style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipmentList as $item)
                    <tr>
                        <td style="font-weight: bold; color: #60a5fa;">{{ $item->code }}</td>
                        <td>{{ $item->name }}</td>
                        <td><span style="color: #9ca3af;">{{ $item->type }}</span></td>
                        <td>{{ $item->location }}</td>
                        <td>
                            @if($item->status === 'ปกติ')
                                <span class="status-badge status-success">ปกติ</span>
                            @elseif($item->status === 'กำลังซ่อม')
                                <span class="status-badge status-working">กำลังซ่อม</span>
                            @elseif($item->status === 'ชำรุด')
                                <span class="status-badge status-waiting">ชำรุด</span>
                            @else
                                <span class="status-badge status-cancelled">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>{{ $item->repair_requests_count }} ครั้ง</td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('ยืนยันที่จะลบอุปกรณ์นี้หรือไม่?')" style="margin: 0; display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    🗑️ ลบ
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #9ca3af;">
                            ไม่พบข้อมูลอุปกรณ์ไฟฟ้าในระบบ
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-box">
        {{ $equipmentList->links() }}
    </div>
</div>
@endsection
