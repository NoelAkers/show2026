<x-layouts::app :title="__('Edit Exhibitor — :name', ['name' => $exhibitor->full_name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.exhibitors.show', $exhibitor)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                {{ $exhibitor->full_name }}
            </flux:button>
            <flux:heading size="xl">Edit Exhibitor</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.exhibitors.update', $exhibitor) }}" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>First Name</flux:label>
                    <flux:input name="first_name" value="{{ old('first_name', $exhibitor->first_name) }}" required autofocus />
                    @error('first_name') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Last Name</flux:label>
                    <flux:input name="last_name" value="{{ old('last_name', $exhibitor->last_name) }}" required />
                    @error('last_name') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" name="email" value="{{ old('email', $exhibitor->email) }}" />
                @error('email') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Phone</flux:label>
                <flux:input name="phone" value="{{ old('phone', $exhibitor->phone) }}" />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Address</flux:label>
                <flux:input name="address" value="{{ old('address', $exhibitor->address) }}" />
                @error('address') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select name="type">
                    <flux:select.option value="adult" :selected="old('type', $exhibitor->type) === 'adult'">Adult</flux:select.option>
                    <flux:select.option value="junior" :selected="old('type', $exhibitor->type) === 'junior'">Junior</flux:select.option>
                </flux:select>
                @error('type') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:checkbox name="is_resident" value="1" :checked="old('is_resident', $exhibitor->is_resident)" />
                <flux:label>Village resident</flux:label>
                @error('is_resident') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:checkbox name="is_novice" value="1" :checked="old('is_novice', $exhibitor->is_novice)" />
                <flux:label>Novice</flux:label>
                @error('is_novice') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.exhibitors.show', $exhibitor)" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
