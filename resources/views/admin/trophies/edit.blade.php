<x-layouts::app :title="__('Edit Trophy — :name', ['name' => $trophy->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.trophies.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Trophies</flux:button>
            <flux:heading size="xl">Edit Trophy</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.trophies.update', $trophy) }}" class="max-w-lg space-y-4"
              x-data="{ awardType: '{{ old('is_points_based', $trophy->is_points_based ? '1' : '0') }}' }">
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

            <flux:field>
                <flux:label>Award Type</flux:label>
                <flux:select name="is_points_based" x-model="awardType">
                    <flux:select.option value="1">Points-based (awarded by class results)</flux:select.option>
                    <flux:select.option value="0">Judge-awarded (selected by a judge)</flux:select.option>
                </flux:select>
                @error('is_points_based') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div x-show="awardType === '1'" class="space-y-4">
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

                <flux:field>
                    <flux:label>Eligibility Restrictions</flux:label>
                    <flux:description>Only exhibitors meeting all selected criteria will be considered for this trophy.</flux:description>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <flux:checkbox name="restrictions[]" value="resident" :checked="in_array('resident', old('restrictions', $trophyRestrictions))" />
                            <span class="text-sm">Residents only</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:checkbox name="restrictions[]" value="novice" :checked="in_array('novice', old('restrictions', $trophyRestrictions))" />
                            <span class="text-sm">Novices only</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:checkbox name="restrictions[]" value="junior" :checked="in_array('junior', old('restrictions', $trophyRestrictions))" />
                            <span class="text-sm">Juniors only</span>
                        </div>
                    </div>
                    @error('restrictions') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <div x-show="awardType === '0'" class="space-y-4">
                <flux:field>
                    <flux:label>Judge</flux:label>
                    @if ($judges->isNotEmpty())
                        <flux:select name="judge_id">
                            <flux:select.option value="">Select a judge…</flux:select.option>
                            @foreach ($judges as $judge)
                                <flux:select.option value="{{ $judge->id }}" :selected="old('judge_id', $trophy->judge_id) == $judge->id">
                                    {{ $judge->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <p class="text-sm text-zinc-500">No judges are set up yet.</p>
                    @endif
                    @error('judge_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Steward</flux:label>
                    @if ($stewards->isNotEmpty())
                        <flux:select name="steward_id">
                            <flux:select.option value="">Select a steward…</flux:select.option>
                            @foreach ($stewards as $steward)
                                <flux:select.option value="{{ $steward->id }}" :selected="old('steward_id', $trophy->steward_id) == $steward->id">
                                    {{ $steward->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <p class="text-sm text-zinc-500">No stewards are set up yet.</p>
                    @endif
                    @error('steward_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>Winning Entry</flux:label>
                    @if ($entries->isNotEmpty())
                        <flux:select name="winning_entry_id">
                            <flux:select.option value="">No winner selected</flux:select.option>
                            @foreach ($entries as $entry)
                                <flux:select.option value="{{ $entry->id }}" :selected="old('winning_entry_id', $trophy->winning_entry_id) == $entry->id">
                                    {{ $entry->formatted_entry_number }} — {{ $entry->exhibitor->full_name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <p class="text-sm text-zinc-500">No entries exist yet.</p>
                    @endif
                    @error('winning_entry_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
                <flux:button :href="route('admin.trophies.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
