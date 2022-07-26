<?php

declare(strict_types=1);

return [
    'debugger' => [
        'is_active' => false,

        'log' => [
            'crawled' => true,
            'should_crawl' => true,
            'short_filename' => true,

            // FilesystemAdapter
            'persist' => true,

            // ScannerController (doesn't rely on is_active)
            'create' => true,
        ],

        'console_log' => [
            'crawler' => true,
            'matcher' => true,
        ],

        'scanner' => true,
        'short_filename' => true,
    ],
];
