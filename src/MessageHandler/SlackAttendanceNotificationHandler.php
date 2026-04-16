<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackAttendanceNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SlackAttendanceNotificationHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $slackWebhookUrl,
    ) {}

    private const PLANET_EMOJIS = [
        'Raccoons of Asgard' => ':raccoon_of_asgard:',
        'Donuts Panda'       => ':donut:',
        'Ducks'              => ':ducks:',
        'Les chatons'        => ':skizo:',
        'Astéroïde'          => '🪨',
    ];

    public function __invoke(SlackAttendanceNotification $message): void
    {
        if (empty($this->slackWebhookUrl)) {
            return;
        }

        $lines = [];
        foreach ($message->byPlanet as $entry) {
            $count = $entry['count'];
            $emoji = self::PLANET_EMOJIS[$entry['planet']] ?? '';
            $prefix = $emoji !== '' ? "{$emoji} " : '';

            if ($count === 0) {
                $lines[] = "{$prefix}Pour la planète *{$entry['planet']}*, aucun astronaute n'était présent";
            } elseif ($count === 1) {
                $lines[] = "{$prefix}Pour la planète *{$entry['planet']}*, 1 astronaute était présent : {$entry['names']}";
            } else {
                $lines[] = "{$prefix}Pour la planète *{$entry['planet']}*, {$count} astronautes étaient présents : {$entry['names']}";
            }
        }

        $body = implode("\n", $lines);

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => [
                'text'   => "🎟️ Présences — *{$message->eventName}*",
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type'  => 'plain_text',
                            'text'  => "🎟️ Présences — {$message->eventName}",
                            'emoji' => true,
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $body,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
