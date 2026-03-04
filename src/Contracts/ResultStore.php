<?php

namespace Intrfce\FFFlags\Contracts;

interface ResultStore
{
    public function get(string $featureClass, ?string $scopeType, string|int|null $scopeId): ?bool;

    public function store(string $featureClass, ?string $scopeType, string|int|null $scopeId, bool $result): void;

    public function delete(string $featureClass, ?string $scopeType, string|int|null $scopeId): void;

    public function purge(): void;
}
