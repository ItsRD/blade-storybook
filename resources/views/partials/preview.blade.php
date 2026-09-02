<header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
    <div>
        <h1 class="text-base font-semibold text-slate-900">{{ $component->name }}</h1>
        <p class="text-xs text-slate-500">
            <code>&lt;x-{{ $component->tag }}&gt;</code>
            @if ($component->description)
                · {{ $component->description }}
            @endif
        </p>
    </div>

    <div class="flex gap-1 rounded-lg bg-slate-100 p-1">
        @foreach ($viewports as $label => $width)
            <button type="button"
                    @click="viewport = {{ $width === null ? 'null' : $width }}"
                    class="rounded-md px-3 py-1 text-xs font-medium"
                    :class="viewport === {{ $width === null ? 'null' : $width }} ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'">
                {{ $label }}
            </button>
        @endforeach
    </div>
</header>

<div class="flex flex-1 justify-center overflow-auto bg-slate-100 p-6">
    <iframe :src="src"
            :style="viewport ? `width: ${viewport}px` : 'width: 100%'"
            class="h-full min-h-[240px] rounded-lg border border-slate-200 bg-white shadow-sm transition-[width]"
            title="Component preview"></iframe>
</div>
