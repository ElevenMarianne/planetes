<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackPointsNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SlackPointsNotificationHandler
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

    public function __invoke(SlackPointsNotification $message): void
    {
        if (empty($this->slackWebhookUrl)) {
            return;
        }

        $planetPart = '';
        if ($message->planetName !== null) {
            $emoji      = self::PLANET_EMOJIS[$message->planetName] ?? '';
            $emojiPart  = $emoji !== '' ? " {$emoji}" : '';
            $planetPart = " de la planète *{$message->planetName}*{$emojiPart}";
        }

        $text = "*{$message->astronautName}*{$planetPart} a gagné *{$message->points} points* grâce à _{$message->activityName}_";

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => [
                'text'   => $text,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => ['type' => 'mrkdwn', 'text' => $text],
                    ],
                ],
            ],
        ]);
    }
}
