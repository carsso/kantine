<?php


$particlesOptionsSnow = [
    'name' => 'Neige',
    "particles" => [
        "number" => [
            "value" => 100,
        ],
        "move" => [
            "direction" => "bottom",
            "enable" => true,
            "random" => false,
            "straight" => false,
        ],
        "opacity" => [
            "value" => [
                "min" => 0.1,
                "max" => 0.5,
            ],
        ],
        "size" => [
            "value" => [
                "min" => 1,
                "max" => 10,
            ],
        ],
        "wobble" => [
            "distance" => 20,
            "enable" => true,
            "speed" => [
                "min" => -5,
                "max" => 5,
            ],
        ],
    ],
];
$particlesOptionsFire = [
    'name' => 'Braises',
    "fpsLimit" => 40,
    "particles" => [
        "number" => [
            "value" => 200,
            "density" => [
                "enable" => true,
            ],
        ],
        "color" => [
            "value" => [
                '#A6D64D',
                '#4AB0F5',
                '#ED733D',
                '#FFD124',
            ]
        ],
        "opacity" => [
            "value" => ["min" => 0.4, "max" => 0.8],
        ],
        "size" => [
            "value" => ["min" => 2, "max" => 4],
        ],
        "move" => [
            "enable" => true,
            "speed" => 3,
            "random" => false,
        ],
    ],
];


$particlesOptionsLinks = [
    'name' => 'Liens',
    "particles" => [
        "number" => [
            "value" => 100,
        ],
        "links" => [
            "distance" => 175,
            "enable" => true,
            "opacity" => 0.5,
        ],
        "move" => [
            "enable" => true,
        ],
        "size" => [
            "value" => 1,
        ],
        "shape" => [
            "type" => "circle",
        ],
    ],
];
$particlesOptionsTriangles = [
    'name' => 'Triangles',
    "particles" => [
        "number" => [
            "value" => 100,
        ],
        "links" => [
            "distance" => 175,
            "enable" => true,
            "opacity" => 0.5,
            "triangles" => [
                "enable" => true,
                "opacity" => 0.02,
            ],
        ],
        "move" => [
            "enable" => true,
            "speed" => 2,
        ],
        "size" => [
            "value" => 1,
        ],
        "shape" => [
            "type" => "circle",
        ],
    ],
];
$particlesOptionsBalls = [
    'name' => 'Balles',
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
                '#A6D64D',
                '#4AB0F5',
                '#ED733D',
                '#FFD124',
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
$particlesOptionsParty = [
    'name' => 'Confettis',
    "fpsLimit" => 120,
    'particles' => [
        'number' => [
            'value' => 0,
        ],
        'color' => [
            'value' => [
                '#A6D64D',
                '#4AB0F5',
                '#ED733D',
                '#FFD124',
            ],
        ],
        'shape' => [
          'type' => [
            'circle',
          ],
        ],
        'size' => [
            'value' => 4,
        ],
        'move' => [
            'enable' => true,
            'direction' => 'top',
            'angle' => [
                'value' => 30,
                'offset' => 0,
            ],
            'speed' => [
                'min' => 10,
                'max' => 30,
            ],
            'gravity' => [
                'enable' => true,
                'acceleration' => 5,
                'inverse' => false,
                'maxSpeed' => 15,
            ],
            'outModes' => [
                'default' => 'destroy',
                'bottom' => 'destroy',
                'left' => 'bounce',
                'right' => 'bounce',
                'top' => 'none',
            ],
        ],
        'rotate' => [
            'value' => [
                'min' => 0,
                'max' => 360,
            ],
            'direction' => 'random',
            'animation' => [
                'enable' => true,
                'speed' => 60,
            ],
        ],
        'tilt' => [
            'direction' => 'random',
            'enable' => true,
            'value' => [
                'min' => 0,
                'max' => 360,
            ],
            'animation' => [
                'enable' => true,
                'speed' => 60,
            ],
        ],
        'roll' => [
            'darken' => [
                'enable' => true,
                'value' => 25
            ],
            'enable' => true,
            'speed' => [
                'min' => 15,
                'max' => 25,
            ],
        ],
        'wobble' => [
            'distance' => 30,
            'enable' => true,
            'speed' => [
                'min' => -15,
                'max' => 15,
            ],
        ],
    ],
    'emitters' => [
        'rate' => [
            'quantity' => 10,
            'delay' => 0.2,
        ],
        'position' => [
            'x' => 50,
            'y' => 100,
        ],
    ],
];

return [
    'config' => [
        'snow' => $particlesOptionsSnow,
        'fire' => $particlesOptionsFire,
        'links' => $particlesOptionsLinks,
        'triangles' => $particlesOptionsTriangles,
        'balls' => $particlesOptionsBalls,
        'party' => $particlesOptionsParty,
    ],
];
