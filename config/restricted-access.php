<?php

return [
    'tables' => [
        'links' => 'restricted_access_links',
        'opens' => 'restricted_access_opens',
    ],
    'route' => [
        'middleware' => ['web'],
        'prefix'     => 'restricted-access-link',
        'as'         => 'restricted-access-link.',
    ],
];
