<x-layouts::app :title="__('Add User')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.users.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                Users
            </flux:button>
            <flux:heading size="xl">Add User</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-lg space-y-4">
            @csrf

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name') }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" name="email" value="{{ old('email') }}" required />
                @error('email') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Phone</flux:label>
                <flux:input name="phone" value="{{ old('phone') }}" />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Role</flux:label>
                <flux:select name="role">
                    @foreach (\App\Enums\UserRole::cases() as $role)
                        <flux:select.option value="{{ $role->value }}" :selected="old('role') === $role->value">
                            {{ ucfirst($role->value) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('role') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <p class="text-sm text-zinc-500">A password reset link will be emailed to the user so they can set their own password.</p>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Add User</flux:button>
                <flux:button :href="route('admin.users.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
