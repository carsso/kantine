<?php


$particlesOptionsSnow = [
    'name' => 'Neige',
    'background' => [
        'color' => '#000E9C',
    ],
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
    'background' => [
        'color' => '#000E9C',
    ],
    "fpsLimit" => 40,
    "particles" => [
        "number" => [
            "value" => 200,
            "density" => [
                "enable" => true,
            ],
        ],
        "color" => [
            "value" => ["#4a90e2", "#50e3c2", "#003f5c", "#2f4b7c", "#6aafd8"]
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
    'background' => [
        'color' => '#000E9C',
    ],
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
    'background' => [
        'color' => '#000E9C',
    ],
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
        'fire' => $particlesOptionsFire,
        'links' => $particlesOptionsLinks,
        'triangles' => $particlesOptionsTriangles,
        'balls' => $particlesOptionsBalls,
    ],
];
