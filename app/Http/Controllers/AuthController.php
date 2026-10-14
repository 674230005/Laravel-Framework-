<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller จัดการการยืนยันตัวตน (Authentication)
 * ครอบคลุม: แสดงหน้าฟอร์มเข้าสู่ระบบ, ตรวจสอบข้อมูลล็อกอิน, และออกจากระบบ
 */
class AuthController extends Controller
{
    /**
     * แสดงหน้าฟอร์มเข้าสู่ระบบ
     * หากผู้ใช้เข้าสู่ระบบอยู่แล้ว ให้เปลี่ยนเส้นทางไปยัง Dashboard โดยอัตโนมัติ
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * ตรวจสอบข้อมูลและดำเนินการเข้าสู่ระบบ
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูลที่ส่งมาจากฟอร์ม (Validation)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        // 2. ลองตรวจสอบการเข้าสู่ระบบด้วยรหัสผ่านที่เข้ารหัสแล้ว (Hash)
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // ป้องกัน Session Fixation Attack
            $request->session()->regenerate();

            $user = Auth::user();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'ยินดีต้อนรับคุณ ' . $user->name . ' เข้าสู่ระบบ');
        }

        // 3. กรณีเข้าสู่ระบบไม่สำเร็จ ให้ส่งกลับพร้อมข้อความแจ้งเตือน
        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง โปรดตรวจสอบอีกครั้ง',
        ])->onlyInput('email');
    }

    /**
     * ออกจากระบบ (Logout) และล้าง Session เพื่อความปลอดภัย
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
