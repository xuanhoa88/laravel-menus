<?php
return [
    'default' => [
        'activate' => [
            'auto' => true,
            'parents' => true,
            'class' => 'active',
            'element' => 'item' // item|link
        ],
        'inheritance' => true,
        'restful' => false,
        'rest_base' => '' // string|array
    ],
    'view' => 'menus::menu'
];