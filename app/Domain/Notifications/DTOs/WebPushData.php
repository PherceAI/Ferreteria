<?php

declare(strict_types=1);

namespace App\Domain\Notifications\DTOs;

use Spatie\LaravelData\Data;

final class WebPushData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $icon = '/icons/icon-192.png',
        public readonly ?string $url = '/dashboard',
        public readonly ?string $tag = null,
        public readonly string $severity = 'info', // info | warning | critical
    ) {}
}
