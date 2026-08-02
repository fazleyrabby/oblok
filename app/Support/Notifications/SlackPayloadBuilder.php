<?php

namespace App\Support\Notifications;

use App\Models\NotificationDelivery;

class SlackPayloadBuilder
{
    /**
     * Build a Slack incoming-webhook-compatible payload for a delivery.
     *
     * @return array{text: string, blocks: array<int, array<string, mixed>>}
     */
    public static function build(NotificationDelivery $delivery): array
    {
        return [
            'text' => $delivery->subject,
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $delivery->subject,
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Severity:*\n{$delivery->severity->label()}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Rule:*\n{$delivery->alertRule->name}",
                        ],
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "Project: {$delivery->project->name}",
                        ],
                    ],
                ],
            ],
        ];
    }
}
