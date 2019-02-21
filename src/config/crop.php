<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Crop types
    |--------------------------------------------------------------------------
    |
    | An array with all crop types of the site, with their width, height
    | and prefix for each of them.
    |
    */

    'types' => [
        [
            // 01 - First crop type
            ['width' => 640, 'height' => 400, 'prefix' => 'thumbs/'],
            ['width' => 240, 'height' => 150, 'prefix' => 'small/']
        ],
    ],

];
