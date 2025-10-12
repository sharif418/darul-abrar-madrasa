<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Notice;
use App\Models\Fee;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a staff user for testing
     */
    private function makeStaffUser(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    public function test_staff_can_access_dashboard(): void
    {
        $staffUser = $this->makeStaffUser();

        $response = $this->actingAs($staffUser)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_staff_can_view_public_notices(): void
    {
        $staffUser = $this->makeStaffUser();
        
        // Create notices for staff and all
        Notice::factory()->create([
            'notice_for' => 'staff',
            'title' => 'Staff Notice',
            'is_active' => true,
        ]);
        Notice::factory()->create([
            'notice_for' => 'all',
            'title' => 'All Notice',
            'is_active' => true,
        ]);
        
        // Create notice for students (should not see)
        Notice::factory()->create([
            'notice_for' => 'students',
            'title' => 'Student Notice',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staffUser)
            ->get('/notices/public');

        $response->assertStatus(200)
            ->assertSee('Staff Notice')
            ->assertSee('All Notice')
            ->assertDontSee('Student Notice');
    }

    public function test_staff_can_view_recent_fees(): void
    {
        $staffUser = $this->makeStaffUser();
        Fee::factory()->count(3)->create();

        $response = $this->actingAs($staffUser)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_admin_routes(): void
    {
        $staffUser = $this->makeStaffUser();

        $this->actingAs($staffUser)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/teachers/create')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/students/create')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/fees/create')
            ->assertStatus(403);
    }

    public function test_staff_cannot_access_teacher_routes(): void
    {
        $staffUser = $this->makeStaffUser();

        $this->actingAs($staffUser)
            ->get('/lesson-plans')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/attendances/create')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/marks/create')
            ->assertStatus(403);
    }

    public function test_staff_cannot_access_student_routes(): void
    {
        $staffUser = $this->makeStaffUser();

        $this->actingAs($staffUser)
            ->get('/my-attendance')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/my-results')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/my-fees')
            ->assertStatus(403);
    }

    public function test_staff_cannot_access_guardian_routes(): void
    {
        $staffUser = $this->makeStaffUser();

        $this->actingAs($staffUser)
            ->get('/guardian/dashboard')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/guardian/children')
            ->assertStatus(403);
    }

    public function test_staff_cannot_access_accountant_routes(): void
    {
        $staffUser = $this->makeStaffUser();

        $this->actingAs($staffUser)
            ->get('/accountant/dashboard')
            ->assertStatus(403);

        $this->actingAs($staffUser)
            ->get('/accountant/fees')
            ->assertStatus(403);
    }

    public function test_staff_can_access_profile(): void
    {
        $staffUser = $this->makeStaffUser();

        $response = $this->actingAs($staffUser)
            ->get('/profile');

        $response->assertStatus(200)
            ->assertSee($staffUser->name)
            ->assertSee($staffUser->email);
    }

    public function test_staff_can_update_own_profile(): void
    {
        $staffUser = $this->makeStaffUser();

        $response = $this->actingAs($staffUser)
            ->put('/profile', [
                'name' => 'Updated Staff Name',
                'email' => $staffUser->email,
            ]);

        $response->assertRedirect('/profile');

        $staffUser->refresh();
        $this->assertEquals('Updated Staff Name', $staffUser->name);
    }
}
