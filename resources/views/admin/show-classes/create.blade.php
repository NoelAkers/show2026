<x-layouts::app :title="__('Add Class — :section', ['section' => $showSection->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.show-sections.show-classes.index', $showSection)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                {{ $showSection->name }}
            </flux:button>
            <flux:heading size="xl">Add Class</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.show-sections.show-classes.store', $showSection) }}" class="max-w-lg space-y-4">
            @csrf

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name') }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description">{{ old('description') }}</flux:textarea>
                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Prize Level</flux:label>
                <flux:select name="prize_level_id">
                    @foreach ($prizeLevels as $prizeLevel)
                        <flux:select.option value="{{ $prizeLevel->id }}" :selected="old('prize_level_id') == $prizeLevel->id || ($loop->first && !old('prize_level_id'))">
                            {{ $prizeLevel->name }} (1st £{{ number_format($prizeLevel->first_place_pence / 100, 2) }}, 2nd £{{ number_format($prizeLevel->second_place_pence / 100, 2) }}, 3rd £{{ number_format($prizeLevel->third_place_pence / 100, 2) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('prize_level_id') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Max Entries per Exhibitor</flux:label>
                <flux:input type="number" name="max_entries_per_exhibitor" value="{{ old('max_entries_per_exhibitor', 5) }}" min="1" required />
                @error('max_entries_per_exhibitor') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Sort Order</flux:label>
                <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" required />
                @error('sort_order') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Create Class</flux:button>
                <flux:button :href="route('admin.show-sections.show-classes.index', $showSection)" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
