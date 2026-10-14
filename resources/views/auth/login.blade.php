
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | ระบบแจ้งซ่อมเครื่องใช้ไฟฟ้า</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI",
                Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #0b1120;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            background: #111827;
            padding: 35px 30px;
            border-radius: 12px;
            border: 1px solid #1e3a8a;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .logo {
            text-align: center;
            font-size: 42px;
            margin-bottom: 8px;
        }

        h1 {
            text-align: center;
            color: #60a5fa;
            font-size: 24px;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #d1d5db;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 7px;
            color: white;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #3b82f6;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .alert-error,
        .alert-success {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #7f1d1d;
            border: 1px solid #b91c1c;
            color: #fecaca;
        }

        .alert-success {
            background: #14532d;
            border: 1px solid #22c55e;
            color: #bbf7d0;
        }

        .demo-accounts {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #1f2937;
        }

        .demo-title {
            color: #9ca3af;
            font-size: 12px;
            margin-bottom: 10px;
            text-align: center;
        }

        .demo-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .btn-demo {
            background: #1f2937;
            border: 1px solid #374151;
            color: #d1d5db;
            padding: 9px 5px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-demo:hover {
            background: #374151;
            color: #60a5fa;
            border-color: #60a5fa;
        }
    </style>
</head>

<body>
<div class="login-box">

    <div class="logo">⚡</div>
    <h1>ระบบแจ้งซ่อม</h1>
    <p class="subtitle">เครื่องใช้ไฟฟ้าภายในบริษัท</p>

    {{-- แสดงข้อความแจ้งเตือน --}}
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ฟอร์มเข้าสู่ระบบ --}}
    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="email">อีเมล</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="กรอกอีเมล"
                required
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="กรอกรหัสผ่าน"
                required
            >
        </div>

        <button type="submit" class="btn-submit">
            เข้าสู่ระบบ
        </button>
    </form>

    {{-- ปุ่มกรอกบัญชีทดสอบทั้ง 3 บทบาท --}}
    <div class="demo-accounts">
        <p class="demo-title">
            คลิกปุ่มเพื่อกรอกบัญชีทดสอบอัตโนมัติ
        </p>

        <div class="demo-buttons">
            <button type="button" class="btn-demo"
                onclick="fillDemo('admin@company.com', 'Admin@12345')">
                👑 แอดมิน
            </button>

            <button type="button" class="btn-demo"
                onclick="fillDemo('technician@company.com', 'Tech@12345')">
                🔧 ช่างซ่อม
            </button>

            <button type="button" class="btn-demo"
                onclick="fillDemo('user1@company.com', 'User@12345')">
                👤 ผู้ใช้งาน
            </button>
        </div>
    </div>

</div>

<script>
    // กรอกอีเมลและรหัสผ่านเมื่อคลิกปุ่มแต่ละบทบาท
    function fillDemo(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>

</body>
</html>
