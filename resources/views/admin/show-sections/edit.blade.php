<x-layouts::app :title="__('Edit Section')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.show-sections.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Sections</flux:button>
            <flux:heading size="xl">Edit Section</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.show-sections.update', $showSection) }}" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name', $showSection->name) }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description">{{ old('description', $showSection->description) }}</flux:textarea>
                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Sort Order</flux:label>
                <flux:input type="number" name="sort_order" value="{{ old('sort_order', $showSection->sort_order) }}" min="0" required />
                @error('sort_order') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.show-sections.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
