<?php
// check_project.php - Proje köküne at: C:\Projects\SMZone\check_project.php
// php check_project.php

echo "=== 1. MODELS VAR MI? ===\n";
$models = [
    'app/Models/Content.php',
    'app/Models/ContentItem.php',
    'app/Models/Product.php',
    'app/Models/AiLearning.php',
    'app/Models/AiSetting.php',
    'app/Enums/ContentStatus.php',
];
foreach ($models as $m) {
    echo file_exists($m) ? "[VAR] $m\n" : "[YOK]  $m\n";
}

echo "\n=== 2. FILAMENT RESOURCES ===\n";
$resources = glob('app/Filament/Resources/*/*.php');
foreach ($resources as $r) {
    echo "$r\n";
}

echo "\n=== 3. DATABASE TABLOLARI (sqlite) ===\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tables = ['contents', 'content_items', 'products', 'ai_learnings', 'ai_settings'];
    foreach ($tables as $table) {
        echo "\n--- Table: $table ---\n";
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            echo "  [YOK] tablo yok\n";
            continue;
        }
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        echo "  Columns: " . implode(', ', $cols) . "\n";
        echo "  has user_id? " . (in_array('user_id', $cols) ? 'EVET' : 'HAYIR') . "\n";
        echo "  has caption? " . (in_array('caption', $cols) ? 'EVET' : 'HAYIR') . "\n";
        echo "  has body? " . (in_array('body', $cols) ? 'EVET' : 'HAYIR') . "\n";
        echo "  count: " . \Illuminate\Support\Facades\DB::table($table)->count() . "\n";
    }
} catch (Exception $e) {
    echo "DB Hatası: " . $e->getMessage() . "\n";
}

echo "\n=== 4. AiWriter.php ANALIZ ===\n";
$aiWriter = glob('app/Filament/Pages/*AiWriter*.php');
$aiWriter = array_merge($aiWriter, glob('app/Filament/Resources/*/*AiWriter*.php'));
$aiWriter = array_merge($aiWriter, glob('app/Livewire/*AiWriter*.php'));
if (empty($aiWriter)) {
    // tüm projede ara
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($it as $file) {
        if (str_contains($file->getFilename(), 'AiWriter')) {
            $aiWriter[] = $file->getPathname();
        }
    }
}
foreach ($aiWriter as $f) {
    echo "Dosya: $f\n";
    $content = file_get_contents($f);
    $checks = ['ContentItem', 'Content::', 'AiLearning', 'user_id', "->create(", "'body'", "'caption'"];
    foreach ($checks as $c) {
        if (str_contains($content, $c)) {
            echo "  içerir: $c\n";
        }
    }
    // ilk 30 satır
    echo "  --- ilk 60 satır ---\n";
    $lines = explode("\n", $content);
    for ($i=0; $i<min(60, count($lines)); $i++) {
        echo ($i+1).": ".$lines[$i]."\n";
    }
    echo "\n";
}

echo "\n=== 5. Content MODEL content ===\n";
foreach (['app/Models/Content.php', 'app/Models/ContentItem.php'] as $m) {
    if (file_exists($m)) {
        echo "\n--- $m ---\n";
        echo file_get_contents($m);
    }
}
