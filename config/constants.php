<?php

use App\Services\Import\ImportCSVStrategy;
use App\Services\Import\ImportJSONStrategy;
use App\Services\Import\ImportXMLStrategy;

return [
    'import'=> [
        'status'=> [
            'pending'=> 'pending',
            'in_progress'=> 'in_progress',
            'completed'=> 'completed'
        ],
        'storage'=> [
            'local'=>'local',
            'url'=>'url'
        ],
        'types'=> [
            'csv'=> ImportCSVStrategy::class,
            'json' => ImportJSONStrategy::class,
            'xml'=> ImportXMLStrategy::class
        ]
    ]
];