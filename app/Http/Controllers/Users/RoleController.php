<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Groups permissions for the checkbox grid, mirroring the sidebar's own
     * section headings so admins recognize what each permission unlocks.
     */
    private const GROUPS = [
        'General' => ['view dashboard'],
        'Sales' => ['access pos', 'view sales', 'void sales', 'manage shifts'],
        'Customers' => ['view customers', 'manage customers'],
        'Inventory' => ['manage products', 'manage categories', 'manage stock'],
        'Purchasing' => ['manage suppliers', 'manage purchase orders', 'manage purchase returns'],
        'Service' => ['manage warranties', 'manage repairs'],
        'Reports' => ['view reports'],
        'Accounting' => ['view accounting', 'manage chart of accounts', 'manage tax settings', 'manage expenses'],
        'Admin' => ['manage users', 'manage roles', 'manage settings', 'manage discount rules', 'manage stores', 'view system health'],
    ];

    public function index(): View
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();

        return view('users.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $groupedPermissions = $this->groupedPermissions();

        return view('users.roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $groupedPermissions = $this->groupedPermissions();
        $assignedPermissions = $role->permissions->pluck('name')->all();

        return view('users.roles.edit', compact('role', 'groupedPermissions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete a role that has users assigned to it.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, Permission>>
     */
    private function groupedPermissions(): array
    {
        $all = Permission::orderBy('name')->get()->keyBy('name');
        $grouped = [];

        foreach (self::GROUPS as $label => $names) {
            $grouped[$label] = collect($names)->map(fn ($name) => $all->get($name))->filter()->values();
        }

        return $grouped;
    }
}
