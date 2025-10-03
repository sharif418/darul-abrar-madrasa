#!/bin/bash

cd /root/darul-abrar-madrasa/darul-abrar-madrasa

echo "=========================================="
echo "Running Migrations Step by Step"
echo "=========================================="
echo ""

# First, let's check current migration status
echo "Current migration status:"
php artisan migrate:status
echo ""

# Run migrations one by one
echo "Step 1: Running main tables migration..."
php artisan migrate --path=/database/migrations/2025_08_27_160354_create_all_madrasa_tables.php --force
echo ""

echo "Step 2: Running grading scales migration..."
php artisan migrate --path=/database/migrations/2025_08_27_191636_create_grading_scales_table.php --force
echo ""

echo "Step 3: Running lesson plans migration..."
php artisan migrate --path=/database/migrations/2025_08_27_191647_create_lesson_plans_table.php --force
echo ""

echo "Step 4: Running study materials migration..."
php artisan migrate --path=/database/migrations/2025_08_27_191700_create_study_materials_table.php --force
echo ""

echo "Step 5: Running additional fields migration..."
php artisan migrate --path=/database/migrations/2025_01_27_000001_add_missing_fields_to_tables.php --force
echo ""

echo "=========================================="
echo "Final migration status:"
php artisan migrate:status
echo ""

echo "Checking created tables:"
php -r "
\$pdo = new PDO('mysql:host=127.0.0.1;dbname=darul_abrar_madrasa', 'madrasa_user', 'Madrasa@2025#Secure');
\$stmt = \$pdo->query('SHOW TABLES');
\$tables = \$stmt->fetchAll(PDO::FETCH_COLUMN);
echo 'Total tables: ' . count(\$tables) . PHP_EOL;
foreach (\$tables as \$table) {
    echo '  - ' . \$table . PHP_EOL;
}
"
echo ""
echo "=========================================="
echo "Migrations Complete!"
echo "=========================================="
