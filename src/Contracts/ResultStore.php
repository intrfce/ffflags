<?php

namespace Intrfce\FFFlags\Contracts;

interface ResultStore
{
    public function get(string $featureSlug, ?string $scopeType, string|int|null $scopeId): ?bool;

    public function store(string $featureSlug, ?string $scopeType, string|int|null $scopeId, bool $result): void;

    public function delete(string $featureSlug, ?string $scopeType, string|int|null $scopeId): void;

    public function purge(): void;
}
