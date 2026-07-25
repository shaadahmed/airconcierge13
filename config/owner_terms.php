<?php

return [
    /*
    | Staff notification recipients for owner terms agree / disagree (legacy parity).
    */
    'agree_to' => env('OWNER_TERMS_AGREE_TO', 'adminteam@airconcierge.net'),
    'agree_bcc' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OWNER_TERMS_AGREE_BCC', 'ryan@airconcierge.net,customercare@airconcierge.net')),
    ))),
    'disagree_to' => env('OWNER_TERMS_DISAGREE_TO', 'ryan@airconcierge.net'),
    'disagree_bcc' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OWNER_TERMS_DISAGREE_BCC', 'customercare@airconcierge.net')),
    ))),
    'from' => [
        'email' => env('OWNER_TERMS_FROM_EMAIL', 'siteupdate@airconcierge.net'),
        'name' => env('OWNER_TERMS_FROM_NAME', 'airconcierge.net'),
    ],
    'terms_of_use_url' => env('OWNER_TERMS_OF_USE_URL', 'https://www.airconcierge.net/terms-of-use'),
];
