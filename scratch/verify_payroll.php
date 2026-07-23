<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = db();
$users = $pdo->query("SELECT * FROM users")->fetchAll();
foreach ($users as $emp) {
    $payroll = calculate_emp_payroll_details($emp, 6, 2026);
    echo "=== PAYROLL DUMP FOR " . $emp['name'] . " (" . $emp['id'] . ") ===\n";
    echo "Expected/Attended: " . $payroll['expected_workdays'] . "/" . $payroll['attended_days'] . "\n";
    echo "Late Days / Late Deductions: " . $payroll['late_days'] . " / " . $payroll['late_deductions'] . "\n";
    echo "Daily Allowance Rate/Pay: " . $payroll['daily_allowance_rate'] . " / " . $payroll['daily_allowance_pay'] . "\n";
    echo "Deductions: " . $payroll['deductions'] . "\n";
    echo "Net Pay: " . $payroll['net'] . "\n\n";
}
