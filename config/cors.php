<?php

return [
    'paths' => ['api/*', 'sse-notifications'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000', 'https://stocksense.me', 'https://www.stocksense.me', 'http://stocksense.test'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Content-Type', 'Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];
