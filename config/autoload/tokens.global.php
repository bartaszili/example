<?php

declare(strict_types=1);

/**
 * Tokens global configuration.
 *
 * Hostname => website allowed to use this application
 * Token => at least 128 characters long, random, unique string
 */

return [
    'tokens' => [
        /**
         * Never change or delete this 1st record !
         */
        ['hostname' => 'master', 'token' => 'token-1'],

        /**
         * Enable: add new row or uncomment
         * Disable: comment out row
         */
        ['hostname' => 'www.example-1.com', 'token' => 'abc'],
    ],
];