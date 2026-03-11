<?php

namespace Intrfce\FFFlags;

class FFFlags
{
    public static function basePath(string $path = ''): string
    {
        return dirname(__DIR__) . ($path !== '' ? '/' . $path : '');
    }
}
