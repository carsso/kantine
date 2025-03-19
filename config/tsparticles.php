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
                '#73E3FF',
                '#147DE8',
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
                '#73E3FF',
                '#147DE8',
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
            'limit' => [
                'mode' => 'delete',
                'value' => 200,
            ],
        ],
        'color' => [
            'value' => [
                '#A6D64D',
                '#4AB0F5',
                '#ED733D',
                '#FFD124',
                '#73E3FF',
                '#147DE8',
            ],
            'animation' => [
                'h' => [
                    'speed' => 10,
                ],
                's' => [
                    'speed' => 1,
                ],
                'l' => [
                    'speed' => 1,
                ],
            ],
        ],
        'size' => [
            'value' => 3,
        ],
        'move' => [
            'enable' => true,
            'direction' => 'top',
            'speed' => [
                'min' => 10,
                'max' => 25,
            ],
            'gravity' => [
                'enable' => true,
                'acceleration' => 9.81,
                'inverse' => false,
                'maxSpeed' => 15,
            ],
            'outModes' => [
                'default' => 'destroy',
                'bottom' => 'destroy',
                'left' => 'destroy',
                'right' => 'destroy',
                'top' => 'none',
            ],
        ],
    ],
    'emitters' => [
        'rate' => [
            'quantity' => 6,
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
