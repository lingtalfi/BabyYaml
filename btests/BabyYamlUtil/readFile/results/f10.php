<?php


$defs = [
    'doo' => ['oi', 'foo'],
    'map' => [
        'one' => 1,
        'two' => 2
    ],
    'nomap' => '{one1, two2}',
    'recur' => [
        'a',
        'b',
        [
            'one' => 1,
            'two' => 'two',
            'three' => [
                "again",
                "again",
            ],
        ],
        'c',
        [1, 2]
    ],
];