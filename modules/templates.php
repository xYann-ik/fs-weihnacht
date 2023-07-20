<?php
/*
    Defines the post card templates
    Config:
        width => width of user uploaded image
        height => height of user uploaded image
        x => distance from left side
        y => distance from top side
        crop => true to crop the uploaded image and force the width/height
        rotate => rotation angle of uploaded image
        overlay => image in /card/ to overlay onto uploaded image
*/
return array(
    'template1' => [
        'width' => 500,
        'height' => 500,
        'x' => '50',
        'y' => '50'
    ],
    'template2' => [
        'width' => 700,
        'height' => 700,
        'x' => '20',
        'y' => 'bottom - 20'
    ],
    'template3' => [
        'width' => '918',
        'height' => '720',
        'x' => 'center + 35',
        'y' => 'center - 21',
        'crop' => true
    ],
    'template4' => [
        'width' => 500,
        'height' => 500,
        'x' => 100,
        'y' => 250
    ],
    'template5' => [
        'width' => 500,
        'height' => 500,
        'x' => 'right - 20',
        'y' => '20'
    ],
    'template6' => [
        'width' => 500,
        'height' => 500,
        'x' => 400,
        'y' => 250
    ]
);

?>