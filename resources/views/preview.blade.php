@php
    $assets = config('blade-storybook.preview.assets', []);
    $viteError = null;
    $viteTags = null;

    try {
        $viteTags = $assets === [] ? null : app(Illuminate\Foundation\Vite::class)($assets);
    } catch (Throwable $exception) {
        $viteError = $exception->getMessage();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $component->name }}</title>
    {!! $viteTags !!}
</head>
<body class="{{ config('blade-storybook.preview.body_class', 'p-8') }}">
    @if ($viteError)
        <div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:.5rem;background:#fef3c7;color:#92400e;font:13px system-ui">
            Assets could not be loaded: {{ $viteError }}
        </div>
    @endif

    @if ($error)
        <div style="padding:.75rem 1rem;border-radius:.5rem;background:#fee2e2;color:#991b1b;font:13px/1.5 ui-monospace,monospace">
            {{ $error }}
        </div>
    @else
        {!! $html !!}
    @endif
</body>
</html>
