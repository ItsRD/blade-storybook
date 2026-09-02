<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Storybook{{ $component ? ' · '.$component->name : '' }}</title>
    <link rel="stylesheet" href="{{ route('blade-storybook.asset', 'storybook.css') }}">
    <script defer src="{{ route('blade-storybook.asset', 'storybook.js') }}"></script>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
    <div class="flex h-full">
        @include('blade-storybook::partials.sidebar')

        @if ($component === null || $state === null)
            <main class="flex flex-1 items-center justify-center p-10 text-center">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">No components found</h1>
                    <p class="mt-2 max-w-md text-sm text-slate-500">
                        Add the <code class="rounded bg-slate-200 px-1">#[Storybook]</code> attribute to a class based
                        Blade component in one of the configured paths.
                    </p>
                </div>
            </main>
        @else
            <div class="flex flex-1 overflow-hidden" x-data="storybook(@js($payload))">
                <main class="flex flex-1 flex-col overflow-hidden">
                    @include('blade-storybook::partials.preview')
                </main>

                @include('blade-storybook::partials.controls')
            </div>
        @endif
    </div>
</body>
</html>
