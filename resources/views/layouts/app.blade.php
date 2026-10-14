<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท')</title>

    {{-- สไตล์กลางของระบบ (Central Theme CSS) ใช้ชุดสีและรูปแบบเดียวกันทุกหน้า --}}
    <style>
        /* รีเซ็ตค่าเริ่มต้นและฟอนต์พื้นฐาน */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* โทนสีหลักตามข้อกำหนด: พื้นหลังน้ำเงินดำ #0b1120 และตัวอักษรสีขาว */
        body {
            background-color: #0b1120;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* แถบเมนูด้านบน (Navbar) สี #111827 เส้นขอบ #1e3a8a */
        .navbar {
            background-color: #111827;
            border-bottom: 1px solid #1e3a8a;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #60a5fa;
            font-size: 20px;
            font-weight: 700;
            text-decoration: none;
        }

        .navbar-brand:hover {
            color: #93c5fd;
        }

        /* เมนูนำทางตรงกลาง */
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            list-style: none;
        }

        .nav-link {
            color: #9ca3af;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #ffffff;
            background-color: #1f2937;
        }

        /* ส่วนแสดงข้อมูลผู้ใช้ด้านขวา */
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-tag {
            font-size: 13px;
            color: #dbeafe;
            background-color: #1f2937;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #374151;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-pill {
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 600;
        }

        .role-admin {
            background-color: #7f1d1d;
            color: #fecaca;
        }

        .role-technician {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        .role-user {
            background-color: #064e3b;
            color: #a7f3d0;
        }

        /* ปุ่มออกจากระบบ */
        .btn-logout {
            color: #ffffff;
            background-color: #1d4ed8;
            border: none;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-logout:hover {
            background-color: #1e40af;
        }

        /* คอนเทนเนอร์หลักของหน้าเว็บ */
        .container {
            max-width: 1140px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
        }

        /* การ์ดคอนเทนต์ สีพื้น #111827 เส้นขอบ #1e3a8a */
        .card {
            background-color: #111827;
            border: 1px solid #1e3a8a;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1f2937;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        /* หัวข้อหน้าเว็บ สีน้ำเงินอ่อน #60a5fa */
        h1, h2, h3, h4 {
            color: #60a5fa;
            font-weight: 600;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 20px;
        }

        /* คำอธิบายรอง สีเทา #9ca3af */
        .subtitle {
            color: #9ca3af;
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* แถบการแจ้งเตือน (Flash Alerts) */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #14532d;
            border: 1px solid #22c55e;
            color: #bbf7d0;
        }

        .alert-danger {
            background-color: #7f1d1d;
            border: 1px solid #ef4444;
            color: #fecaca;
        }

        /* ปุ่มกดมาตรฐาน (Buttons) */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #374151;
            color: #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            color: #ffffff;
        }

        .btn-success {
            background-color: #16a34a;
            color: #ffffff;
        }

        .btn-success:hover {
            background-color: #15803d;
        }

        .btn-danger {
            background-color: #dc2626;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 13px;
        }

        /* แบบฟอร์มและอินพุต */
        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #dbeafe;
            font-size: 14px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 11px 14px;
            background-color: #1f2937;
            border: 1px solid #374151;
            border-radius: 6px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-error {
            color: #f87171;
            font-size: 13px;
            margin-top: 5px;
        }

        /* ตารางข้อมูล (Tables) */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #1e3a8a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #111827;
            text-align: left;
        }

        th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 13px 16px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        td {
            padding: 13px 16px;
            border-bottom: 1px solid #1f2937;
            color: #dbeafe;
            font-size: 14px;
            vertical-align: middle;
        }

        tr:hover td {
            background-color: #172033;
        }

        /* ป้ายสถานะงานซ่อม (Status Badges) ตรงตามข้อกำหนดในข้อ 4 */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* โทนเหลืองสำหรับรอดำเนินการ */
        .status-waiting {
            background-color: #78350f;
            color: #fbbf24;
            border: 1px solid #b45309;
        }

        /* โทนน้ำเงินสำหรับระหว่างดำเนินงาน */
        .status-working {
            background-color: #1e3a8a;
            color: #93c5fd;
            border: 1px solid #3b82f6;
        }

        /* โทนเขียวสำหรับซ่อมเสร็จ */
        .status-success {
            background-color: #14532d;
            color: #86efac;
            border: 1px solid #22c55e;
        }

        /* โทนแดงสำหรับยกเลิก */
        .status-cancelled {
            background-color: #7f1d1d;
            color: #fca5a5;
            border: 1px solid #ef4444;
        }

        /* ป้ายระดับความเร่งด่วน (Priority Badges) */
        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .priority-urgent {
            background-color: #991b1b;
            color: #fecaca;
        }

        .priority-high {
            background-color: #c2410c;
            color: #fed7aa;
        }

        .priority-medium {
            background-color: #1e3a8a;
            color: #bfdbfe;
        }

        .priority-low {
            background-color: #374151;
            color: #d1d5db;
        }

        /* การแบ่งหน้า (Pagination) */
        .pagination-box {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .pagination-box nav {
            display: flex;
            gap: 5px;
        }

        /* ส่วนท้าย (Footer) */
        footer {
            background-color: #111827;
            border-top: 1px solid #1e3a8a;
            padding: 18px 20px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            margin-top: auto;
        }

        /* ปุ่มแฮมเบอร์เกอร์สำหรับหน้าจอมือถือ */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* รองรับการแสดงผลทุกขนาดหน้าจอ (Responsive) */
        @media (max-width: 860px) {
            .navbar {
                padding: 12px 20px;
                flex-wrap: wrap;
            }

            .mobile-menu-btn {
                display: block;
            }

            .navbar-nav {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                padding-top: 15px;
                gap: 5px;
            }

            .navbar-nav.show {
                display: flex;
            }

            .navbar-user {
                width: 100%;
                justify-content: space-between;
                padding-top: 12px;
                margin-top: 10px;
                border-top: 1px solid #1f2937;
            }

            .container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .card {
                padding: 18px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    {{-- แถบเมนูด้านบน (Header / Navbar) --}}
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <span>⚡</span>
            <span>Electrical Repair System</span>
        </a>

        <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="เปิดเมนู">
            ☰
        </button>

        {{-- เมนูการนำทาง แบ่งตามบทบาทของผู้ใช้ --}}
        <ul class="navbar-nav" id="navbarMenu">
            <li>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    🏠 หน้าหลัก
                </a>
            </li>

            {{-- เมนูสำหรับผู้ใช้งานทั่วไป และทุกคน --}}
            <li>
                <a href="{{ route('repairs.create') }}" class="nav-link {{ request()->routeIs('repairs.create') ? 'active' : '' }}">
                    📝 แจ้งซ่อม
                </a>
            </li>
            <li>
                <a href="{{ route('repairs.index') }}" class="nav-link {{ request()->routeIs('repairs.index') ? 'active' : '' }}">
                    🔧 รายการแจ้งซ่อม
                </a>
            </li>
            <li>
                <a href="{{ route('repairs.status') }}" class="nav-link {{ request()->routeIs('repairs.status') ? 'active' : '' }}">
                    📊 ติดตามสถานะ
                </a>
            </li>

            {{-- เมนูเพิ่มเติมสำหรับช่างซ่อม (Technician) --}}
            @if(Auth::check() && (Auth::user()->isTechnician() || Auth::user()->isAdmin()))
                <li>
                    <a href="{{ route('technician.repairs.index') }}" class="nav-link {{ request()->routeIs('technician.*') ? 'active' : '' }}">
                        🛠️ งานช่างของฉัน
                    </a>
                </li>
            @endif

            {{-- เมนูเพิ่มเติมสำหรับผู้ดูแลระบบ (Admin) --}}
            @if(Auth::check() && Auth::user()->isAdmin())
                <li>
                    <a href="{{ route('admin.repairs.index') }}" class="nav-link {{ request()->routeIs('admin.repairs.*') ? 'active' : '' }}">
                        ⚙️ จัดการงานทั้งหมด
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        👥 จัดการผู้ใช้
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.equipment.index') }}" class="nav-link {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}">
                        📦 อุปกรณ์ไฟฟ้า
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        📈 รายงาน
                    </a>
                </li>
            @endif
        </ul>

        {{-- ข้อมูลผู้ใช้และปุ่มออกจากระบบ --}}
        @if(Auth::check())
            <div class="navbar-user">
                <div class="user-tag">
                    <span>{{ Auth::user()->name }}</span>
                    @if(Auth::user()->isAdmin())
                        <span class="role-pill role-admin">Admin</span>
                    @elseif(Auth::user()->isTechnician())
                        <span class="role-pill role-technician">ช่างซ่อม</span>
                    @else
                        <span class="role-pill role-user">ผู้ใช้งาน</span>
                    @endif
                </div>

                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        🚪 ออกจากระบบ
                    </button>
                </form>
            </div>
        @endif
    </nav>

    {{-- คอนเทนต์หลักของแต่ละหน้า --}}
    <main class="container">

        {{-- แสดงข้อความแจ้งเตือนสำเร็จ (Flash Success) --}}
        @if(session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- แสดงข้อความแจ้งเตือนผิดพลาด (Flash Error) --}}
        @if(session('error'))
            <div class="alert alert-danger">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- แทรกเนื้อหาของแต่ละหน้าด้วย Blade @yield --}}
        @yield('content')

    </main>

    {{-- ส่วนท้ายหน้าเว็บ (Footer) --}}
    <footer>
        <p>&copy; {{ date('Y') }} ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้าภายในบริษัท (Electrical Repair System) - โครงงานระบบสารสนเทศ</p>
    </footer>

    {{-- สคริปต์สลับเปิด-ปิดเมนูบนอุปกรณ์มือถือ --}}
    <script>
        function toggleMenu() {
            var menu = document.getElementById('navbarMenu');
            menu.classList.toggle('show');
        }
    </script>
    @yield('scripts')
</body>
</html>
