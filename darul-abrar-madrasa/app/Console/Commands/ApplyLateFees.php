<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Repositories\FeeRepository;
use App\Services\ActivityLogService;

class ApplyLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --dry-run           Show what would be done without applying
     *  --fee-type=         Apply only to a specific fee type (e.g., monthly, exam)
     *  --force             Skip confirmation prompts
     */
    protected $signature = 'fees:apply-late-fees 
                            {--dry-run : Show actions without applying changes}
                            {--fee-type= : Restrict processing to a specific fee type}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Apply late fees to overdue fees based on policies';

    public function __construct(
        protected FeeRepository $fees,
        protected ActivityLogService $activity
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $feeType = $this->option('fee-type');
        $force = (bool) $this->option('force');

        if (!$force && !$dryRun) {
            if (!$this->confirm('This will apply late fees to overdue records. Continue?')) {
                $this->info('Aborting.');
                return self::SUCCESS;
            }
        }

        $today = Carbon::today();
        $this->info("Scanning overdue fees as of {$today->toDateString()}".($feeType ? " for type: {$feeType}" : '') . ($dryRun ? ' [DRY RUN]' : ''));

        // Fetch overdue fees via repository. We assume a method exists or fall back to simple query logic.
        // To keep this command repository-oriented, we call a repo method if implemented:
        // $overdueFees = $this->fees->getOverdueFees($feeType);
        // If not available, we attempt a safe fallback using the Fee model.
        try {
            if (method_exists($this->fees, 'getOverdueFees')) {
                $overdueFees = $this->fees->getOverdueFees($feeType);
            } else {
                // Fallback: query using the Fee model statically to avoid tight coupling
                $feeModel = app(\App\Models\Fee::class);
                $query = $feeModel->newQuery()
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->whereDate('due_date', '<', $today);
                if ($feeType) {
                    $query->where('fee_type', $feeType);
                }
                $overdueFees = $query->get();
            }
        } catch (\Throwable $e) {
            $this->error('Failed to fetch overdue fees: '.$e->getMessage());
            Log::error('ApplyLateFees: failed to fetch overdue fees', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        $count = 0;
        $totalLateFees = 0.0;

        foreach ($overdueFees as $fee) {
            try {
                if ($dryRun) {
                    // Calculate projected late fee without applying
                    if (method_exists($this->fees, 'calculateAndApplyLateFees')) {
                        // If repo only applies, attempt to calculate via model helper if exists
                        $lateAmount = method_exists($fee, 'calculateLateFee') ? (float) $fee->calculateLateFee() : 0.0;
                    } else {
                        $lateAmount = method_exists($fee, 'calculateLateFee') ? (float) $fee->calculateLateFee() : 0.0;
                    }

                    $this->line(sprintf(
                        '[DRY] Fee #%s student:%s type:%s overdue since %s late-fee: ৳ %s',
                        $fee->id,
                        $fee->student_id ?? '-',
                        $fee->fee_type ?? '-',
                        optional($fee->due_date)->toDateString() ?? '-',
                        number_format($lateAmount, 2)
                    ));
                    $totalLateFees += (float) $lateAmount;
                    $count++;
                    continue;
                }

                // Apply late fee using repository if available
                if (method_exists($this->fees, 'calculateAndApplyLateFees')) {
                    $applied = (float) $this->fees->calculateAndApplyLateFees($fee->id);
                } else {
                    // Fallback: use model-level helpers if defined
                    if (method_exists($fee, 'calculateLateFee') && method_exists($fee, 'applyLateFee')) {
                        $applied = (float) $fee->calculateLateFee();
                        if ($applied > 0) {
                            $fee->applyLateFee($applied);
                            $fee->save();
                        }
                    } else {
                        $applied = 0.0;
                    }
                }

                $this->line(sprintf(
                    'Applied late fee: fee #%s student:%s type:%s late-fee: ৳ %s',
                    $fee->id,
                    $fee->student_id ?? '-',
                    $fee->fee_type ?? '-',
                    number_format((float) $applied, 2)
                ));

                if ($applied > 0) {
                    $this->activity->logLateFeeApplication($fee, $applied, [
                        'days_overdue' => $this->daysOverdue($fee->due_date, $today),
                        'policy_used' => $fee->fee_type ?? null,
                    ]);
                }

                $totalLateFees += (float) $applied;
                $count++;
            } catch (\Throwable $e) {
                $this->warn(sprintf('Failed processing fee #%s: %s', $fee->id ?? '-', $e->getMessage()));
                Log::warning('ApplyLateFees: failed to process fee', [
                    'fee_id' => $fee->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        $summary = sprintf(
            'Processed %d overdue fees. %s total late fees: ৳ %s',
            $count,
            $dryRun ? 'Projected' : 'Applied',
            number_format($totalLateFees, 2)
        );

        $dryRun ? $this->comment($summary) : $this->info($summary);
        Log::info('ApplyLateFees summary', ['count' => $count, 'total' => $totalLateFees, 'dry_run' => $dryRun, 'fee_type' => $feeType]);

        return self::SUCCESS;
    }

    protected function daysOverdue($dueDate, Carbon $today): ?int
    {
        try {
            $due = $dueDate instanceof Carbon ? $dueDate->copy() : Carbon::parse($dueDate);
            return $due ? $due->diffInDays($today) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
