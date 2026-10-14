@extends('layouts.app')

@section('title', 'แก้ไขบัญชีผู้ใช้ ' . $user->name . ' (Admin)')

@section('content')
<div style="max-width: 650px; margin: 0 auto;">

    <div class="card">
        <div class="card-header">
            <div>
                <h1>✏️ แก้ไขข้อมูลบัญชีผู้ใช้</h1>
                <p class="subtitle" style="margin-bottom: 0;">แก้ไขข้อมูลส่วนตัว บทบาท หรือเปลี่ยนรหัสผ่าน</p>
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

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">ชื่อ - นามสกุล <span style="color: #ef4444;">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">อีเมล <span style="color: #ef4444;">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">รหัสผ่านใหม่ (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="กรอกเฉพาะเมื่อต้องการเปลี่ยนรหัสผ่านใหม่"
                >
            </div>

            <div class="form-group">
                <label for="role">บทบาทในระบบ <span style="color: #ef4444;">*</span></label>
                <select id="role" name="role" required>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>👤 ผู้ใช้งานทั่วไป (User)</option>
                    <option value="technician" {{ old('role', $user->role) == 'technician' ? 'selected' : '' }}>🔧 ช่างซ่อม (Technician)</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์ติดต่อ</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                >
            </div>

            <div class="form-group">
                <label for="department">แผนก / ฝ่ายที่สังกัด</label>
                <input
                    type="text"
                    id="department"
                    name="department"
                    value="{{ old('department', $user->department) }}"
                >
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px;">
                    💾 อัปเดตข้อมูลผู้ใช้
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
