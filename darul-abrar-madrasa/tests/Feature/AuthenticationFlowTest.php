<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Accountant;
use Illuminate\Support\Facades\Hash;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('Login');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_admin_redirects_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('Admin Dashboard');
    }

    public function test_teacher_redirects_to_teacher_dashboard_after_login(): void
    {
        $teacher = Teacher::factory()->create();
        $user = $teacher->user;
        $user->password = Hash::make('password');
        $user->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('Teacher Dashboard');
    }

    public function test_student_redirects_to_student_dashboard_after_login(): void
    {
        $student = Student::factory()->create();
        $user = $student->user;
        $user->password = Hash::make('password');
        $user->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('Student Dashboard');
    }

    public function test_guardian_redirects_to_guardian_dashboard_after_login(): void
    {
        $guardian = Guardian::factory()->create();
        $user = $guardian->user;
        $user->password = Hash::make('password');
        $user->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_accountant_redirects_to_accountant_dashboard_after_login(): void
    {
        $accountant = Accountant::factory()->create();
        $user = $accountant->user;
        $user->password = Hash::make('password');
        $user->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->get('/users')
            ->assertRedirect('/login');

        $this->get('/my-attendance')
            ->assertRedirect('/login');
    }

    public function test_password_reset_link_request_page_is_accessible(): void
    {
        $response = $this->get('/password/reset');

        $response->assertStatus(200);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_user_cannot_request_reset_for_nonexistent_email(): void
    {
        $response = $this->post('/password/email', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_redirects_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertCookie('remember_web');
    }
}
