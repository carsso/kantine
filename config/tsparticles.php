<?php

/*
 * Every style disables HDR: tsparticles v4 enables it by default and then emits
 * colors as color(display-p3 ...) with a 400/203 factor. The components exceed
 * 1, get clipped, and everything washes out to white on an SDR screen.
 * hdr => false restores the v3 rgba()/hsla() output.
 *
 * Colors live under particles.paint.color since v4 (previously
 * particles.color); the old key is silently ignored and paint.color defaults
 * to #fff, hence all-white particles.
 *
 * Opacity goes through paint.fill.opacity, not particles.opacity: in
 * tsparticles 4.3.2, Particle.getOpacity() already folds opacity into
 * fillOpacity, then RenderManager multiplies by opacity again. The final alpha
 * is opacity^2 and everything looks washed out. Keeping particles.opacity at 1
 * (1^2 = 1) and putting the value on paint.fill.opacity renders correctly today
 * and will keep doing so once the upstream bug is fixed.
 */

$particlesOptionsSnow = [
    'name' => 'Neige',
    'hdr' => false,
    'particles' => [
        'number' => [
            'value' => 100,
        ],
        'move' => [
            'direction' => 'bottom',
            'enable' => true,
            'random' => false,
            'straight' => false,
        ],
        'paint' => [
            'fill' => [
                'opacity' => [
                    'min' => 0.1,
                    'max' => 0.5,
                ],
            ],
        ],
        'size' => [
            'value' => [
                'min' => 1,
                'max' => 10,
            ],
        ],
        'wobble' => [
            'distance' => 20,
            'enable' => true,
            'speed' => [
                'min' => -5,
                'max' => 5,
            ],
        ],
    ],
];
$particlesOptionsFire = [
    'name' => 'Braises',
    'hdr' => false,
    'fpsLimit' => 40,
    'particles' => [
        'number' => [
            'value' => 200,
            'density' => [
                'enable' => true,
            ],
        ],
        'paint' => [
            'color' => [
                'value' => [
                    '#A6D64D',
                    '#4AB0F5',
                    '#ED733D',
                    '#FFD124',
                ],
            ],
            'fill' => [
                'opacity' => ['min' => 0.4, 'max' => 0.8],
            ],
        ],
        'size' => [
            'value' => ['min' => 2, 'max' => 4],
        ],
        'move' => [
            'enable' => true,
            'speed' => 3,
            'random' => false,
        ],
    ],
];

$particlesOptionsLinks = [
    'name' => 'Liens',
    'hdr' => false,
    'particles' => [
        'number' => [
            'value' => 100,
        ],
        'links' => [
            'distance' => 175,
            'enable' => true,
            'opacity' => 0.5,
        ],
        'move' => [
            'enable' => true,
        ],
        'size' => [
            'value' => 1,
        ],
        'shape' => [
            'type' => 'circle',
        ],
    ],
];
$particlesOptionsTriangles = [
    'name' => 'Triangles',
    'hdr' => false,
    'particles' => [
        'number' => [
            'value' => 100,
        ],
        'links' => [
            'distance' => 175,
            'enable' => true,
            'opacity' => 0.5,
            'triangles' => [
                'enable' => true,
                'opacity' => 0.02,
            ],
        ],
        'move' => [
            'enable' => true,
            'speed' => 2,
        ],
        'size' => [
            'value' => 1,
        ],
        'shape' => [
            'type' => 'circle',
        ],
    ],
];
$particlesOptionsBalls = [
    'name' => 'Balles',
    'hdr' => false,
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
        'paint' => [
            'color' => [
                'value' => [
                    '#A6D64D',
                    '#4AB0F5',
                    '#ED733D',
                    '#FFD124',
                ],
            ],
            'fill' => [
                'opacity' => 0.5,
            ],
        ],
        'shape' => [
            'type' => 'circle',
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
    'hdr' => false,
    'fpsLimit' => 120,
    'particles' => [
        'number' => [
            'value' => 0,
        ],
        'paint' => [
            'color' => [
                'value' => [
                    '#A6D64D',
                    '#4AB0F5',
                    '#ED733D',
                    '#FFD124',
                ],
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
                'value' => 25,
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
