<?php


$particlesOptionsSnow = [
    'name' => 'Neige',
    'background' => [
        'color' => '#000E9C',
    ],
    'preset' => 'snow',
];
$particlesOptionsLinks = [
    'name' => 'Liens',
    'background' => [
        'color' => '#000E9C',
    ],
    'preset' => 'links',
];
$particlesOptionsTriangles = [
    'name' => 'Triangles',
    'background' => [
        'color' => '#000E9C',
    ],
    'preset' => 'triangles',
    'particles' => [
        'move' => [
            'speed' => 2,
        ],
        'links' => [
            'distance' => 175,
            'triangles' => [
                'opacity' => 0.02,
            ],
        ]
    ],
];
$particlesOptionsBalls = [
    'name' => 'Balles',
    'background' => [
        'color' => '#000E9C',
    ],
    'particles' => [
        'destroy' => [
            'mode' => 'split',
            'split' => [
                'count' => 1,
                'factor' => [
                    'value' => [
                        'min' => 2,
                        'max' => 4,
                    ],
                ],
                'rate' => [
                    'value' => 100,
                ],
                'particles' => [
                    'life' => [
                        'count' => 1,
                        'duration' => [
                            'value' => [
                                'min' => 2,
                                'max' => 3,
                            ],
                        ],
                    ],
                    'move' => [
                        'speed' => [
                            'min' => 10,
                            'max' => 15,
                        ],
                    ],
                ],
            ],
        ],
        'number' => [
            'value' => 80,
        ],
        'color' => [
            'value' => [
                '#3998D0',
                '#2EB6AF',
                '#A9BD33',
                '#FEC73B',
                '#F89930',
                '#F45623',
                '#D62E32',
                '#EB586E',
                '#9952CF',
            ],
        ],
        'shape' => [
            'type' => 'circle',
        ],
        'opacity' => [
            'value' => 0.5,
        ],
        'size' => [
            'value' => [
                'min' => 10,
                'max' => 15,
            ],
        ],
        'collisions' => [
            'enable' => true,
            'mode' => 'bounce',
        ],
        'move' => [
            'enable' => true,
            'speed' => 3,
            'outModes' => 'bounce',
        ],
    ],
];

return [
    'config' => [
        'snow' => $particlesOptionsSnow,
        'links' => $particlesOptionsLinks,
        'triangles' => $particlesOptionsTriangles,
        'balls' => $particlesOptionsBalls,
    ],
];
