
<?php
// config/notifications.php
return [
    'channels' => [
        'mail' => [
            'transport' => 'mail',
            'channel' => 'mail',
        ],
        'database' => [
            'transport' => 'database',
            'channel' => 'database',
        ],
        'broadcast' => [
            'transport' => 'broadcast',
            'channel' => 'broadcast',
        ],
        'nexmo' => [
            'transport' => 'nexmo',
            'channel' => 'sms',
        ],
        'slack' => [
            'transport' => 'slack',
            'channel' => '#notifications',
        ],
    ],

    'default' => 'mail',

    'routes' => [
        'mail' => [
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
        ],
    ],
];
