<?php

/*
 * Chaque style desactive le HDR : tsparticles v4 l'active par defaut et emet
 * alors les couleurs en color(display-p3 ...) avec un facteur 400/203. Les
 * composantes depassent 1, sont ecretees, et tout est delave vers le blanc sur
 * un ecran SDR. hdr => false retablit la sortie rgba()/hsla() de la v3.
 *
 * Les couleurs vivent sous particles.paint.color depuis la v4 (avant :
 * particles.color) ; l'ancienne clef est ignoree en silence et paint.color vaut
 * #fff par defaut, d'ou des particules toutes blanches.
 *
 * L'opacite passe par paint.fill.opacity et non par particles.opacity : dans
 * tsparticles 4.3.2, Particle.getOpacity() replie deja opacity dans fillOpacity,
 * puis RenderManager remultiplie par opacity. L'alpha final vaut donc opacity^2
 * et tout parait delave. En laissant particles.opacity a 1 (1^2 = 1) et en
 * portant la valeur sur paint.fill.opacity, le rendu est correct aujourd'hui et
 * le restera si le bug amont est corrige.
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
