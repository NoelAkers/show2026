<x-layouts::app :title="__('Edit Trophy — :name', ['name' => $trophy->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.trophies.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Trophies</flux:button>
            <flux:heading size="xl">Edit Trophy</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.trophies.update', $trophy) }}" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name', $trophy->name) }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:input name="description" value="{{ old('description', $trophy->description) }}" />
                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            @if ($sections->isNotEmpty())
                <flux:field>
                    <flux:label>Assigned Classes</flux:label>
                    <div class="space-y-3">
                        @foreach ($sections as $section)
                            @if ($section->showClasses->isNotEmpty())
                                <div>
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $section->name }}</p>
                                    <div class="space-y-1 pl-2">
                                        @foreach ($section->showClasses as $class)
                                            <div class="flex items-center gap-2">
                                                <flux:checkbox
                                                    name="class_ids[]"
                                                    value="{{ $class->id }}"
                                                    :checked="in_array($class->id, old('class_ids', $trophy->showClasses->pluck('id')->toArray()))"
                                                />
                                                <span class="text-sm">{{ $class->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @error('class_ids') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            @endif

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.trophies.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
