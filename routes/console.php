<?php
/**
 * School Management System - Console Routes
 * دستورات خط فرمان
 */

use App\Core\Application;

// Only run in CLI mode
if (php_sapi_name() !== 'cli') {
    return;
}

$app = Application::getInstance();

// Register console commands
$commands = [
    'migrate' => 'Database\MigrationRunner',
    'migrate:rollback' => 'Database\MigrationRunner@rollback',
    'migrate:refresh' => 'Database\MigrationRunner@refresh',
    'migrate:status' => 'Database\MigrationRunner@status',
    'seed' => 'Database\SeederRunner',
    'cache:clear' => 'App\Services\CacheService@clear',
    'log:clear' => 'App\Services\LogService@clear',
    'backup:create' => 'App\Services\BackupService@create',
    'backup:restore' => 'App\Services\BackupService@restore',
    'send:reminders' => 'App\Services\NotificationService@sendFeeReminders',
    'process:payroll' => 'App\Services\PayrollService@processMonthly',
    'update:attendance' => 'App\Services\AttendanceService@autoMarkAbsent',
    'generate:fees' => 'App\Services\FinanceService@generateMonthlyFees',
];

$command = $argv[1] ?? null;
$params = array_slice($argv, 2);

if (!$command) {
    echo "School Management System - Console Commands\n";
    echo "============================================\n\n";
    echo "Available commands:\n\n";
    
    foreach ($commands as $name => $handler) {
        echo "  \033[32m{$name}\033[0m\n";
    }
    
    echo "\nUsage: php console.php [command]\n";
    exit;
}

if (isset($commands[$command])) {
    $handler = $commands[$command];
    
    if (strpos($handler, '@') !== false) {
        list($class, $method) = explode('@', $handler);
        $class = "App\\{$class}";
        $instance = new $class();
        $instance->$method(...$params);
    } else {
        $class = "App\\{$handler}";
        $instance = new $class();
        if (method_exists($instance, 'run')) {
            $instance->run();
        }
    }
} else {
    echo "Command not found: {$command}\n";
}