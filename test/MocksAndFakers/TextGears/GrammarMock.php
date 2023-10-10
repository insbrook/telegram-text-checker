<?php

namespace TelegramApp\Test\MocksAndFakers\TextGears;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class GrammarMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'status' => true,
        'response' => [
            'result' => true,
            'errors' => [
                0 => [
                    'id' => 'e1109244478',
                    'offset' => 2,
                    'length' => 4,
                    'description' => [
                        'en' => 'Did you mean “go”?',
                    ],
                    'bad' => 'goes',
                    'better' => [
                        0 => 'go',
                    ],
                    'type' => 'grammar',
                ],
                1 => [
                    'id' => 'e1344585675',
                    'offset' => 17,
                    'length' => 8,
                    'description' => [
                        'en' => 'Did you mean “every day”?',
                    ],
                    'bad' => 'ever day',
                    'better' => [
                        0 => 'every day',
                    ],
                    'type' => 'grammar',
                ],
                2 => [
                    'id' => 'e110688864',
                    'offset' => 47,
                    'length' => 2,
                    'description' => [
                        'en' => 'Use a third-person plural verb.',
                    ],
                    'bad' => 'is',
                    'better' => [
                        0 => 'are',
                    ],
                    'type' => 'grammar',
                ],
            ],
        ],
    ];
}