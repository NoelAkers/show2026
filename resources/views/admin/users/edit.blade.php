<x-layouts::app :title="__('Edit User — :name', ['name' => $user->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.users.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                Users
            </flux:button>
            <flux:heading size="xl">Edit User</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input name="name" value="{{ old('name', $user->name) }}" required autofocus />
                @error('name') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" name="email" value="{{ old('email', $user->email) }}" required />
                @error('email') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Phone</flux:label>
                <flux:input name="phone" value="{{ old('phone', $user->phone) }}" />
                @error('phone') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <flux:field>
                <flux:label>Role</flux:label>
                @if ($isSelf)
                    <flux:input value="{{ ucfirst($user->role->value) }}" disabled />
                    <flux:description>You cannot change your own role.</flux:description>
                    <input type="hidden" name="role" value="{{ $user->role->value }}" />
                @else
                    <flux:select name="role">
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <flux:select.option value="{{ $role->value }}" :selected="old('role', $user->role->value) === $role->value">
                                {{ ucfirst($role->value) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
                @error('role') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.users.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
