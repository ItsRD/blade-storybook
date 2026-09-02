@php
    use ItsRD\BladeStorybook\Metadata\ControlType;
@endphp

<aside class="flex w-80 shrink-0 flex-col gap-6 overflow-y-auto border-l border-slate-200 bg-white px-4 py-5">
    <div>
        <p class="mb-2 text-[11px] font-semibold tracking-wide text-slate-400 uppercase">Story</p>

        <select @change="selectStory($event.target.value)"
                class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
            @foreach ($component->stories() as $story)
                <option value="{{ $story->id() }}" @selected($state->storyId === $story->id())>{{ $story->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($component->props()->contains->isControllable())
        <div>
            <p class="mb-2 text-[11px] font-semibold tracking-wide text-slate-400 uppercase">Props</p>

            <div class="space-y-3">
                @foreach ($component->props() as $prop)
                    @continue (! $prop->isControllable())

                    <div>
                        <label class="mb-1 flex items-baseline justify-between text-xs font-medium text-slate-700">
                            <span>{{ $prop->name }}</span>
                            <span class="font-mono text-[10px] text-slate-400">{{ $prop->type }}</span>
                        </label>

                        @if ($prop->control === ControlType::Select)
                            <select x-model="props['{{ $prop->name }}']"
                                    class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                @foreach ($prop->selectOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($prop->control === ControlType::Toggle)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" x-model="props['{{ $prop->name }}']"
                                       class="rounded border-slate-300">
                                <span x-text="props['{{ $prop->name }}'] ? 'true' : 'false'"></span>
                            </label>
                        @elseif ($prop->control === ControlType::Number)
                            <input type="number" x-model="props['{{ $prop->name }}']"
                                   class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                        @else
                            <input type="text" x-model="props['{{ $prop->name }}']"
                                   class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                        @endif

                        @if ($prop->description)
                            <p class="mt-1 text-[11px] text-slate-500">{{ $prop->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($component->unmanagedProps()->isNotEmpty())
        <p class="-mt-4 text-[11px] leading-relaxed text-slate-400">
            Not marked with <code class="rounded bg-slate-100 px-1">#[Prop]</code>, so not shown:
            {{ $component->unmanagedProps()->map->name->implode(', ') }}.
        </p>
    @endif

    @if ($component->slots()->isNotEmpty())
        <div>
            <p class="mb-2 text-[11px] font-semibold tracking-wide text-slate-400 uppercase">Slots</p>

            <div class="space-y-3">
                @foreach ($component->slots() as $slot)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ $slot->name }}</label>
                        <input type="text" x-model="slots['{{ $slot->name }}']"
                               class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($component->blockingProps()->isNotEmpty())
        <div class="rounded-md bg-amber-50 p-3 text-xs text-amber-800">
            Cannot be previewed. These props need values that are not literals:
            {{ $component->blockingProps()->map->name->implode(', ') }}.
        </div>
    @endif
</aside>
