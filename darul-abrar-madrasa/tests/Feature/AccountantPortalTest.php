<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\FeeWaiver;
use App\Models\LateFeePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountantPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccountantUser()
    {
        $accountant = \Database\Factories\AccountantFactory::new()->create();
        return $accountant->user;
    }

    public function test_accountant_can_access_dashboard(): void
    {
        $user = $this->makeAccountantUser();

        $this->actingAs($user)
            ->get('/accountant/dashboard')
            ->assertStatus(200);
    }

    public function test_accountant_can_record_payment(): void
    {
        $user = $this->makeAccountantUser();

        $fee = \Database\Factories\FeeFactory::new()->create([
            'amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->post("/accountant/fees/{$fee->id}/process-payment", [
                'amount' => 600,
                'payment_method' => 'cash',
                'transaction_id' => 'TXN123',
            ])
            ->assertRedirect('/accountant/fees');

        $fee->refresh();
        $this->assertSame('partial', $fee->status);
        $this->assertEquals(600.00, (float) $fee->paid_amount);

        // Pay remaining
        $this->actingAs($user)
            ->post("/accountant/fees/{$fee->id}/process-payment", [
                'amount' => 400,
                'payment_method' => 'cash',
            ])
            ->assertRedirect('/accountant/fees');

        $fee->refresh();
        $this->assertSame('paid', $fee->status);
        $this->assertEquals(1000.00, (float) $fee->paid_amount);
    }

    public function test_accountant_can_create_waiver(): void
    {
        $user = $this->makeAccountantUser();
        $student = \Database\Factories\StudentFactory::new()->create();

        $this->actingAs($user)
            ->post('/accountant/waivers', [
                'student_id' => $student->id,
                'waiver_type' => 'scholarship',
                'amount_type' => 'percentage',
                'amount' => 20,
                'reason' => 'Merit',
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect('/accountant/waivers');

        $this->assertDatabaseHas('fee_waivers', [
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_accountant_with_approval_permission_can_approve_waiver(): void
    {
        $user = $this->makeAccountantUser();
        // Ensure limits allow approval
        $user->accountant->update([
            'can_approve_waivers' => true,
            'max_waiver_amount' => 10000,
        ]);

        $fee = \Database\Factories\FeeFactory::new()->create(['amount' => 2000]);
        $waiver = FeeWaiver::create([
            'student_id' => $fee->student_id,
            'fee_id' => $fee->id,
            'waiver_type' => 'financial_aid',
            'amount_type' => 'fixed',
            'amount' => 500,
            'reason' => 'Need-based',
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/accountant/waivers/{$waiver->id}/approve")
            ->assertRedirect();

        $waiver->refresh();
        $this->assertSame('approved', $waiver->status);
    }

    public function test_accountant_cannot_approve_waiver_above_limit(): void
    {
        $user = $this->makeAccountantUser();
        $user->accountant->update([
            'can_approve_waivers' => true,
            'max_waiver_amount' => 300,
        ]);

        $fee = \Database\Factories\FeeFactory::new()->create(['amount' => 2000]);
        $waiver = FeeWaiver::create([
            'student_id' => $fee->student_id,
            'fee_id' => $fee->id,
            'waiver_type' => 'financial_aid',
            'amount_type' => 'fixed',
            'amount' => 500, // exceeds limit
            'reason' => 'Need',
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post("/accountant/waivers/{$waiver->id}/approve")
            ->assertSessionHas('error'); // Controller returns back with error message on AuthorizationException or we expect 302 with error
    }

    public function test_accountant_can_create_installment_plan(): void
    {
        $user = $this->makeAccountantUser();

        $fee = \Database\Factories\FeeFactory::new()->create(['amount' => 1200]);

        $this->actingAs($user)
            ->post("/accountant/fees/{$fee->id}/installments", [
                'number_of_installments' => 4,
                'start_date' => now()->toDateString(),
                'frequency' => 'monthly',
            ])
            ->assertRedirect('/accountant/installments');

        $fee->refresh();
        $this->assertCount(4, $fee->installments()->get());
        $this->assertEquals(1200.00, (float) $fee->installments()->sum('amount'));
    }

    public function test_accountant_can_apply_late_fees(): void
    {
        $user = $this->makeAccountantUser();

        // Create an overdue fee
        $fee = \Database\Factories\FeeFactory::new()->create([
            'amount' => 1000,
            'due_date' => now()->subDays(10)->toDateString(),
            'status' => 'unpaid',
        ]);

        // Create a simple fixed policy
        LateFeePolicy::create([
            'name' => 'Default',
            'fee_type' => null,
            'grace_period_days' => 0,
            'calculation_type' => 'fixed',
            'amount' => 100,
            'max_late_fee' => 500,
            'compound' => false,
            'exclude_holidays' => true,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post('/accountant/late-fees/apply')
            ->assertRedirect();

        $fee->refresh();
        $this->assertGreaterThan(0, (float) ($fee->late_fee_total ?? 0));
    }

    public function test_accountant_can_generate_reports(): void
    {
        $user = $this->makeAccountantUser();

        $this->actingAs($user)->get('/accountant/reports')->assertStatus(200);
        $this->actingAs($user)->get('/accountant/reports/collection')->assertStatus(200);
        $this->actingAs($user)->get('/accountant/reports/outstanding')->assertStatus(200);
        $this->actingAs($user)->get('/accountant/reports/waivers')->assertStatus(200);
    }

    public function test_non_accountant_cannot_access_accountant_portal(): void
    {
        // Create a regular student user
        $student = \Database\Factories\StudentFactory::new()->create();
        $user = $student->user;

        $this->actingAs($user)
            ->get('/accountant/dashboard')
            ->assertStatus(403);
    }
}
