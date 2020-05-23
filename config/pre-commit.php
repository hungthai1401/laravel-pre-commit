<?php

return [
    'rules' => [
        'standard' => __DIR__ . '/../phpcs.xml',
        'ignored' => [
            '*/database/*',
            '*/public/*',
            '*/assets/*',
            '*/vendor/*',
        ],
    ],
];
