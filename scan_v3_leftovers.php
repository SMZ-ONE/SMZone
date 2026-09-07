<?php
// scan_v3_leftovers.php - Projedeki v3 kalıntılarını bulur
// php scan_v3_leftovers.php

$path = __DIR__ . '/app/Filament/Resources';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

$patterns = [
    'Filament\Forms\Form' => 'Form $form yerine Schema $schema kullan',
    'Tables\Actions\EditAction' => 'Tables\Actions yerine Filament\Actions kullan',
    'Tables\Actions\DeleteAction' => 'Tables\Actions yerine Filament\Actions kullan',
    'Tables\Actions\BulkActionGroup' => 'ToolbarActions + BulkActionGroup',
    '->actions([' => '->recordActions([ olmalı v4 te',
    '->bulkActions([' => '->toolbarActions([ olmalı v4 te',
];

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    foreach ($patterns as $needle => $msg) {
        if (str_contains($content, $needle)) {
            echo "[BULUNDU] {$file->getPathname()}\n  -> {$needle} : {$msg}\n\n";
        }
    }
}
echo "Tarama bitti.\n";
