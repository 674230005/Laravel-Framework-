<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: RoleMiddleware
 * ใช้สำหรับตรวจสอบสิทธิ์การเข้าถึงหน้าเว็บตามบทบาทของผู้ใช้ (Role-based Authorization)
 * เช่น role:admin, role:technician, role:user หรือระบุหลายบทบาท role:admin,technician
 */
class RoleMiddleware
{
    /**
     * ดำเนินการตรวจสอบสิทธิ์ของผู้ใช้งานในคำขอ
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles บทบาทที่ได้รับอนุญาต
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน');
        }

        $user = Auth::user();

        // 2. ถ้าไม่ได้ระบุ role ถือว่าอนุญาตให้ผ่าน (แค่ล็อกอินแล้ว)
        if (empty($roles)) {
            return $next($request);
        }

        // 3. ตรวจสอบว่า role ของผู้ใช้ตรงกับหนึ่งใน role ที่อนุญาตหรือไม่
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. หากไม่มีสิทธิ์ ให้ส่งกลับหน้า Dashboard พร้อมแจ้งเตือน
        return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนงานนี้');
    }
}
