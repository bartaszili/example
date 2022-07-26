<?php

declare(strict_types=1);

use GuzzleHttp\RequestOptions;

return [
    'socks5_proxy' => [
        'guzzle_http_client' => [
            RequestOptions::PROXY => '127.0.0.1:9050',
            'curl' => [CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5],
            RequestOptions::HEADERS => [
                'User-Agent' => 'my app name',
            ],
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::VERIFY => false,
        ],
        'user_agents' => [
            'my app name...',
        ],
    ],
];