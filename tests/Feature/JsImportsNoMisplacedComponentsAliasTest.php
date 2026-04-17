<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('ningún archivo en resources/js importa @/Components/ (alias incorrecto en Linux)', function (): void {
    $files = File::allFiles(resource_path('js'));

    foreach ($files as $file) {
        $ext = $file->getExtension();
        if (! in_array($ext, ['js', 'jsx', 'ts', 'tsx'], true)) {
            continue;
        }

        $contents = File::get($file->getPathname());

        expect($contents)->not->toContain('@/Components/');
    }
});
