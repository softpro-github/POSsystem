@php $assigned = $assignedPermissions ?? []; @endphp

<div>
    <x-input-label for="name" value="Role Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $role->name ?? '') }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label value="Permissions" />
    <div class="mt-1 space-y-4">
        @foreach ($groupedPermissions as $group => $permissions)
            @if ($permissions->isNotEmpty())
                <div class="border border-border-strong rounded-md p-3">
                    <div class="text-xs font-semibold text-ink-subtle uppercase tracking-wider mb-2">{{ $group }}</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm text-ink">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $assigned))) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
                                {{ ucfirst($permission->name) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
</div>
