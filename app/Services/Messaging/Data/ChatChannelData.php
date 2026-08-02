<?php

namespace App\Services\Messaging\Data;

final class ChatChannelData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}
