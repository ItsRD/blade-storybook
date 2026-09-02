<aside class="flex w-64 shrink-0 flex-col overflow-y-auto border-r border-slate-200 bg-white">
    <div class="border-b border-slate-200 px-4 py-4">
        <p class="text-sm font-semibold text-slate-900">Storybook</p>
        <p class="text-xs text-slate-500">{{ $categories->flatten()->count() }} components</p>
    </div>

    <nav class="flex-1 px-2 py-3">
        @foreach ($categories as $category => $items)
            <p class="px-2 pt-3 pb-1 text-[11px] font-semibold tracking-wide text-slate-400 uppercase">
                {{ $category }}
            </p>

            @foreach ($items as $item)
                <a href="{{ route('blade-storybook.index', ['component' => $item->id()]) }}"
                   class="block rounded-md px-2 py-1.5 text-sm {{ $component?->id() === $item->id() ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $item->name }}
                </a>
            @endforeach
        @endforeach
    </nav>
</aside>
