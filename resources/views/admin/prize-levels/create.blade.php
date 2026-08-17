<x-layouts::app :title="__('Create Prize Level')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.prize-levels.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Prize Levels</flux:button>
            <flux:heading size="xl">Create Prize Level</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.prize-levels.store') }}" class="max-w-lg space-y-4">
            @csrf

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name') }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>1st Place (pence)</flux:label>
                <flux:input type="number" name="first_place_pence" value="{{ old('first_place_pence', 0) }}" min="0" required />
                @error('first_place_pence') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>2nd Place (pence)</flux:label>
                <flux:input type="number" name="second_place_pence" value="{{ old('second_place_pence', 0) }}" min="0" required />
                @error('second_place_pence') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>3rd Place (pence)</flux:label>
                <flux:input type="number" name="third_place_pence" value="{{ old('third_place_pence', 0) }}" min="0" required />
                @error('third_place_pence') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Create Prize Level</flux:button>
                <flux:button :href="route('admin.prize-levels.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
