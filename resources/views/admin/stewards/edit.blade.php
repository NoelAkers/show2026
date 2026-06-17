<x-layouts::app :title="__('Edit Steward — :name', ['name' => $steward->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.stewards.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                Stewards
            </flux:button>
            <flux:heading size="xl">Edit Steward</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.stewards.update', $steward) }}" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name', $steward->name) }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" name="email" value="{{ old('email', $steward->email) }}" required />
                @error('email') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Phone</flux:label>
                <flux:input name="phone" value="{{ old('phone', $steward->phone) }}" />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            @if ($sections->isNotEmpty())
                <flux:field>
                    <flux:label>Assigned Sections</flux:label>
                    <div class="space-y-2">
                        @foreach ($sections as $section)
                            <div class="flex items-center gap-2">
                                <flux:checkbox
                                    name="section_ids[]"
                                    value="{{ $section->id }}"
                                    :checked="in_array($section->id, old('section_ids', $steward->assignedSections->pluck('id')->toArray()))"
                                />
                                <span class="text-sm">{{ $section->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    @error('section_ids') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            @endif

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.stewards.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
