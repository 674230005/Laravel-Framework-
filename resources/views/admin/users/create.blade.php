@extends('layouts.app')

@section('title', 'เพิ่มผู้ใช้งานใหม่ (Admin) | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div style="max-width: 650px; margin: 0 auto;">

    <div class="card">
        <div class="card-header">
            <div>
                <h1>+ เพิ่มบัญชีผู้ใช้งานใหม่</h1>
                <p class="subtitle" style="margin-bottom: 0;">สร้างบัญชีสำหรับผู้ใช้ทั่วไป ช่างซ่อม หรือผู้ดูแลระบบ</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                &larr; กลับหน้ารายชื่อ
            </a>
        </div>

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

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">ชื่อ - นามสกุล <span style="color: #ef4444;">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="เช่น สมชาย ช่างไฟ"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">อีเมล <span style="color: #ef4444;">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="เช่น tech@company.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">รหัสผ่านเริ่มต้น <span style="color: #ef4444;">*</span></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="ความยาวอย่างน้อย 6 ตัวอักษร"
                    required
                >
            </div>

            <div class="form-group">
                <label for="role">บทบาทในระบบ <span style="color: #ef4444;">*</span></label>
                <select id="role" name="role" required>
                    <option value="">-- เลือกบทบาท --</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>👤 ผู้ใช้งานทั่วไป (User - พนักงานในบริษัท)</option>
                    <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>🔧 ช่างซ่อม (Technician - รับงานและปิดงาน)</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin - มอบหมายงานและจัดการระบบ)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์ติดต่อ</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="เช่น 081-234-5678"
                >
            </div>

            <div class="form-group">
                <label for="department">แผนก / ฝ่ายที่สังกัด</label>
                <input
                    type="text"
                    id="department"
                    name="department"
                    value="{{ old('department') }}"
                    placeholder="เช่น ฝ่ายซ่อมบำรุง, ฝ่ายบัญชี, ฝ่ายขาย"
                >
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px;">
                    💾 บันทึกผู้ใช้งานใหม่
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
