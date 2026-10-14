<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * ทดสอบการทำงานพื้นฐานของหน้าแรกและการเปลี่ยนเส้นทาง
 */
class ExampleTest extends TestCase
{
    /**
     * ทดสอบว่าเมื่อเปิดหน้าแรก (/) ระบบจะเปลี่ยนเส้นทางไปยังหน้าเข้าสู่ระบบ (/login)
     */
    public function test_the_application_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');

        // ตรวจสอบว่าระบบส่ง Redirect (302) ไปยังหน้า login
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * ทดสอบว่าหน้าเข้าสู่ระบบ (/login) สามารถเปิดได้สมบูรณ์ (HTTP 200)
     */
    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ระบบแจ้งซ่อม');
    }
}
