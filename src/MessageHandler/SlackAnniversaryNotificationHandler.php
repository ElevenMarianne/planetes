<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackAnniversaryNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SlackAnniversaryNotificationHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $slackWebhookUrl,
    ) {}

    public function __invoke(SlackAnniversaryNotification $message): void
    {
        if (empty($this->slackWebhookUrl)) {
            return;
        }

        $yearLabel = $message->years === 1 ? 'an' : 'ans';
        $planet    = $message->planetName ? " · _{$message->planetName}_" : '';
        $emoji     = match (true) {
            $message->years >= 10 => '🏆',
            $message->years >= 5  => '⭐',
            $message->years >= 3  => '🚀',
            default               => '🎂',
        };

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => [
                'text' => "{$emoji} Anniversaire d'arrivée pour *{$message->astronautName}* !",
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "{$emoji} *{$message->astronautName}* fête aujourd'hui ses *{$message->years} {$yearLabel}* chez Eleven Labs !{$planet}\n> +{$message->points} points d'ancienneté attribués",
                        ],
                    ],
                ],
            ],
        ]);
    }
}
