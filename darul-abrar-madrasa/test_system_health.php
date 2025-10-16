<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing System Health Data Collection...\n\n";

try {
    // Test the query that was failing
    $userIds = \App\Models\User::pluck('id');
    echo "✓ User IDs collected: " . $userIds->count() . " users\n";
    
    $orphanedTeachers = \App\Models\Teacher::whereNotIn('user_id', $userIds)->limit(200)->get();
    echo "✓ Orphaned teachers query works: " . $orphanedTeachers->count() . " found\n";
    
    $orphanedStudents = \App\Models\Student::whereNotIn('user_id', $userIds)->limit(200)->get();
    echo "✓ Orphaned students query works: " . $orphanedStudents->count() . " found\n";
    
    $orphanedGuardians = \App\Models\Guardian::whereNotIn('user_id', $userIds)->limit(200)->get();
    echo "✓ Orphaned guardians query works: " . $orphanedGuardians->count() . " found\n";
    
    $orphanedAccountants = \App\Models\Accountant::whereNotIn('user_id', $userIds)->limit(200)->get();
    echo "✓ Orphaned accountants query works: " . $orphanedAccountants->count() . " found\n";
    
    echo "\n✅ All queries working successfully!\n";
    echo "\nThe System Health dashboard should now work.\n";
    echo "Please refresh your browser and try accessing /admin/system-health\n";
    
} catch (\Throwable $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
