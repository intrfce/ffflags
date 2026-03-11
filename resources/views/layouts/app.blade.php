<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="ffflags-base-url" content="{{ url(config('ffflags.path', 'ffflags')) }}">
    <title>@yield('title', 'FFFlags')</title>
    <style>{!! file_get_contents(\Intrfce\FFFlags\FFFlags::basePath('dist/ffflags.css')) !!}</style>
</head>
<body>
    @yield('content')
    <script>{!! file_get_contents(\Intrfce\FFFlags\FFFlags::basePath('dist/ffflags.js')) !!}</script>
</body>
</html>
