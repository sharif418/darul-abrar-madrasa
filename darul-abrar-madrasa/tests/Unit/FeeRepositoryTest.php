<?php

namespace Tests\Unit;

use App\Models\Fee;
use App\Models\FeeWaiver;
use App\Models\LateFeePolicy;
use App\Repositories\FeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FeeRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(FeeRepository::class);
    }

    public function test_generate_invoice_number_creates_unique_number(): void
    {
        // Create two fees via repository to trigger invoice generation
        $fee1 = $this->repo->create([
            'student_id' => \Database\Factories\StudentFactory::new()->create()->id,
            'fee_type' => 'monthly',
            'amount' => 500,
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'unpaid',
        ]);
        $fee2 = $this->repo->create([
            'student_id' => \Database\Factories\StudentFactory::new()->create()->id,
            'fee_type' => 'monthly',
            'amount' => 700,
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'unpaid',
        ]);

        $this->assertNotEmpty($fee1->invoice_number);
        $this->assertNotEmpty($fee2->invoice_number);
        $this->assertNotEquals($fee1->invoice_number, $fee2->invoice_number);
        $this->assertMatchesRegularExpression('/^' . preg_quote(env('INVOICE_PREFIX', 'INV'), '/') . '-\d{4}-\d{2}-\d{5}$/', $fee1->invoice_number);
    }

    public function test_apply_waiver_to_fee_reduces_amount(): void
    {
        $fee = \Database\Factories\FeeFactory::new()->create(['amount' => 1000]);

        $waiver = FeeWaiver::create([
            'student_id' => $fee->student_id,
            'waiver_type' => 'scholarship',
            'amount_type' => 'percentage',
            'amount' => 20, // 20%
            'reason' => 'Merit',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
            'status' => 'approved',
            'created_by' => 1,
        ]);

        $this->repo->applyWaiverToFee($fee->id, $waiver->id);

        $fee->refresh();
        $this->assertEquals(800.00, (float) $fee->net_amount); // 1000 - 200
    }

    public function test_create_installment_plan_distributes_amount_evenly(): void
    {
        $fee = \Database\Factories\FeeFactory::new()->create(['amount' => 1200]);

        $this->repo->createInstallmentPlan($fee->id, 4, now()->toDateString(), 'monthly');

        $installments = $fee->installments()->orderBy('installment_number')->get();
        $this->assertCount(4, $installments);
        // Sum to fee amount and base amount per installment matches floor division distribution
        $this->assertEquals(1200.00, (float) $installments->sum('amount'));
        $this->assertEquals(300.00, (float) $installments->first()->amount);
    }

    public function test_calculate_and_apply_late_fees_respects_grace_period(): void
    {
        // Policy with grace period of 3 days, fixed 100 late fee
        LateFeePolicy::create([
            'name' => 'Grace Policy',
            'fee_type' => null,
            'grace_period_days' => 3,
            'calculation_type' => 'fixed',
            'amount' => 100,
            'max_late_fee' => 500,
            'compound' => false,
            'exclude_holidays' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);

        // Overdue by 2 days (within grace) -> no late fee
        $fee = \Database\Factories\FeeFactory::new()->create([
            'amount' => 1000,
            'due_date' => now()->subDays(2)->toDateString(),
            'status' => 'unpaid',
        ]);
        $this->repo->calculateAndApplyLateFees($fee->id);
        $fee->refresh();
        $this->assertEquals(0.0, (float) ($fee->late_fee_total ?? 0));

        // Overdue by 5 days (exceeds grace) -> applies 100
        $fee->update(['due_date' => now()->subDays(5)->toDateString()]);
        $this->repo->calculateAndApplyLateFees($fee->id);
        $fee->refresh();
        $this->assertEquals(100.0, (float) ($fee->late_fee_total ?? 0));
    }

    public function test_calculate_and_apply_late_fees_respects_max_limit(): void
    {
        LateFeePolicy::create([
            'name' => 'Cap Policy',
            'fee_type' => null,
            'grace_period_days' => 0,
            'calculation_type' => 'daily',
            'amount' => 50, // 50 per day
            'max_late_fee' => 200, // cap at 200
            'compound' => false,
            'exclude_holidays' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);

        $fee = \Database\Factories\FeeFactory::new()->create([
            'amount' => 1000,
            'due_date' => now()->subDays(10)->toDateString(),
            'status' => 'unpaid',
        ]);

        $this->repo->calculateAndApplyLateFees($fee->id);
        $fee->refresh();
        $this->assertEquals(200.0, (float) ($fee->late_fee_total ?? 0));
    }

    public function test_record_payment_updates_status_correctly(): void
    {
        $fee = \Database\Factories\FeeFactory::new()->create([
            'amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // Partial payment
        $this->repo->recordPayment($fee, ['amount' => 600, 'payment_method' => 'cash']);
        $fee->refresh();
        $this->assertEquals('partial', $fee->status);
        $this->assertEquals(600.00, (float) $fee->paid_amount);

        // Full payment
        $this->repo->recordPayment($fee, ['amount' => 400, 'payment_method' => 'cash']);
        $fee->refresh();
        $this->assertEquals('paid', $fee->status);
        $this->assertEquals(1000.00, (float) $fee->paid_amount);
    }

    public function test_get_statistics_calculates_correctly(): void
    {
        // Create some fees
        \Database\Factories\FeeFactory::new()->create(['amount' => 1000, 'paid_amount' => 1000, 'status' => 'paid']);
        \Database\Factories\FeeFactory::new()->create(['amount' => 500, 'paid_amount' => 200, 'status' => 'partial']);
        \Database\Factories\FeeFactory::new()->create(['amount' => 300, 'paid_amount' => 0, 'status' => 'unpaid']);

        $stats = $this->repo->getStatistics();

        $this->assertEquals(1800.00, (float) $stats['totalFees']);
        $this->assertEquals(1200.00, (float) $stats['collectedFees']);
        $this->assertEquals(600.00, (float) $stats['pendingFees']);
        $this->assertEquals(1, $stats['paidCount']);
        $this->assertEquals(1, $stats['unpaidCount']);
        $this->assertEquals(1, $stats['partialCount']);
    }

    public function test_get_applicable_waivers_returns_active_only(): void
    {
        $student = \Database\Factories\StudentFactory::new()->create();

        // Active waiver
        FeeWaiver::create([
            'student_id' => $student->id,
            'waiver_type' => 'scholarship',
            'amount_type' => 'percentage',
            'amount' => 10,
            'reason' => 'Active',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
            'status' => 'approved',
            'created_by' => 1,
        ]);

        // Expired waiver
        FeeWaiver::create([
            'student_id' => $student->id,
            'waiver_type' => 'scholarship',
            'amount_type' => 'percentage',
            'amount' => 10,
            'reason' => 'Expired',
            'valid_from' => now()->subMonth(),
            'valid_until' => now()->subDay(),
            'status' => 'approved',
            'created_by' => 1,
        ]);

        $waivers = $this->repo->getApplicableWaivers($student->id);

        $this->assertCount(1, $waivers);
        $this->assertEquals('Active', $waivers->first()->reason);
    }
}
