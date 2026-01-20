<?php

declare(strict_types=1);

use OpenSearch\GuzzleClientFactory;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$client = new GuzzleClientFactory()->create(
    [
        'base_uri' => 'http://opensearch:9200',
        'auth' => ['admin', 'admin'],
        'verify' => false,
    ]
);

if ($client->indices()->exists(['index' => 'books']) === false) {
    $client->indices()->create(
        [
            'index' => 'books',
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                    'analysis' => [
                        'filter' => [
                            'ru_stop' => [
                                'type' => 'stop',
                                'stopwords' => '_russian_',
                            ],
                            'ru_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'russian',
                            ],
                        ],
                        'analyzer' => [
                            'ru_analyzer' => [
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'ru_stop', 'ru_stemmer'],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'ru_analyzer',
                            'search_analyzer' => 'ru_analyzer',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                    'ignore_above' => 256,
                                ],
                            ],
                        ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'strict_date_optional_time||epoch_millis',
                        ],
                    ],
                ],
            ],
        ]
    );
}

//$book = [
//    'id'         => 1,
//    'title'      => 'Мастер и Маргарита',
//    'created_at' => '2025-01-01T10:00:00Z',
//];
//$client->create(
//    [
//        'index' => 'books',
//        'id'    => $book['id'],
//        'body'  => $book,
//    ]
//);

$params = [
    'index' => 'books',
    'body' => [
        'query' => [
            'match' => [
                'title' => 'мастер',
            ],
        ],
    ],
];

$response = $client->search($params);
foreach ($response['hits']['hits'] as $hit) {
    echo $hit['_source']['id'] . ' - ' . $hit['_source']['title'] . PHP_EOL;
}
