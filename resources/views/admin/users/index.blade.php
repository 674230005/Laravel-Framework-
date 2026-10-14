@extends('layouts.app')

@section('title', 'จัดการบัญชีผู้ใช้งาน (Admin) | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1>👥 จัดการบัญชีผู้ใช้และบัญชีช่าง</h1>
            <p class="subtitle" style="margin-bottom: 0;">
                ดูแลจัดการรายชื่อผู้ใช้งาน ช่างซ่อม และผู้ดูแลระบบในองค์กร
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + เพิ่มผู้ใช้งานใหม่
        </a>
    </div>

    {{-- แถบตัวกรองและค้นหา --}}
    <form action="{{ route('admin.users.index') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <div style="flex: 1; min-width: 220px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 ค้นหาชื่อ, อีเมล, แผนก..."
            >
        </div>

        <div style="width: 180px;">
            <select name="role" onchange="this.form.submit()">
                <option value="">-- บทบาททั้งหมด --</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin)</option>
                <option value="technician" {{ request('role') == 'technician' ? 'selected' : '' }}>🔧 ช่างซ่อม (Technician)</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>👤 ผู้ใช้งานทั่วไป (User)</option>
            </select>
        </div>

        @if(request()->hasAny(['search', 'role']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                ล้างตัวกรอง
            </a>
        @endif
    </form>

    {{-- ตารางรายชื่อผู้ใช้งาน --}}
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>อีเมล</th>
                    <th>บทบาท</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>แผนก / ฝ่าย</th>
                    <th>วันที่ลงทะเบียน</th>
                    <th style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td style="font-weight: 600; color: #ffffff;">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->isAdmin())
                                <span class="role-pill role-admin">👑 Admin</span>
                            @elseif($u->isTechnician())
                                <span class="role-pill role-technician">🔧 ช่างซ่อม</span>
                            @else
                                <span class="role-pill role-user">👤 ผู้ใช้ทั่วไป</span>
                            @endif
                        </td>
                        <td>{{ $u->phone ?: '-' }}</td>
                        <td>{{ $u->department ?: '-' }}</td>
                        <td style="color: #9ca3af; font-size: 13px;">{{ $u->created_at->format('d/m/Y') }}</td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-secondary btn-sm">
                                    ✏️ แก้ไข
                                </a>

                                @if($u->id !== Auth::id())
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('ยืนยันที่จะลบบัญชีผู้ใช้นี้หรือไม่?')" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            🗑️ ลบ
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #9ca3af;">
                            ไม่พบข้อมูลผู้ใช้งาน
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-box">
        {{ $users->links() }}
    </div>
</div>
@endsection
