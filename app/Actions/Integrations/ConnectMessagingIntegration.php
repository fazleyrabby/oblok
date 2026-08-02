<?php

namespace App\Actions\Integrations;

use App\Enums\MessagingPlatform;
use App\Models\MessagingIntegration;
use App\Models\Project;
use App\Services\Messaging\Exceptions\MessagingApiException;
use App\Services\Messaging\MessagingDriverRegistry;

class ConnectMessagingIntegration
{
    public function __construct(private readonly MessagingDriverRegistry $drivers) {}

    /**
     * Link a chat platform to a project and store its encrypted credentials.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws MessagingApiException When the platform rejects the credentials.
     */
    public function handle(
        Project $project,
        MessagingPlatform $platform,
        array $credentials,
        ?string $channel = null
    ): MessagingIntegration {
        $driver = $this->drivers->for($platform);

        $connection = $driver->verify($credentials);

        return MessagingIntegration::updateOrCreate(
            ['project_id' => $project->id, 'platform' => $platform->value],
            [
                'name' => $connection['name'],
                'config' => $connection['config'],
                'channel' => $channel,
                'enabled' => true,
                'last_connected_at' => now(),
            ]
        );
    }
}
