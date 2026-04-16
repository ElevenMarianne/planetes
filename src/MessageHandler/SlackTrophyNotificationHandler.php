<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SlackTrophyNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SlackTrophyNotificationHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $slackWebhookUrl,
    ) {}

    public function __invoke(SlackTrophyNotification $message): void
    {
        if (empty($this->slackWebhookUrl)) {
            return;
        }

        $emoji = $message->isPlanet ? '🪐' : '👨‍🚀';
        $type = $message->isPlanet ? 'La planète' : '';
        $seasonInfo = $message->seasonName ? " (saison {$message->seasonName})" : '';

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => [
                'text' => "{$emoji} {$type} *{$message->recipientName}* a reçu le trophée *{$message->trophyName}*{$seasonInfo} 🏆",
            ],
        ]);
    }
}
