<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemUserController extends Controller
{
    public function create(): View
    {
        return view('pages.security.users.create', [
            'user' => new User([
                'role' => User::ROLE_WAREHOUSE,
                'is_active' => true,
            ]),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $user = User::create($data);

        ActivityLog::record('user.created', $user, [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()
            ->route('security.index')
            ->with('success', 'Benutzer wurde erstellt.');
    }

    public function edit(User $user): View
    {
        return view('pages.security.users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (! $data['is_active'] && $this->isLastActiveManager($user)) {
            return back()
                ->withInput()
                ->with('error', 'Der letzte aktive Manager darf nicht deaktiviert werden.');
        }

        if ($user->id === auth()->id() && ! $data['is_active']) {
            return back()
                ->withInput()
                ->with('error', 'Du kannst deinen eigenen Account nicht deaktivieren.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $oldRole = $user->role;
        $oldActive = $user->is_active;

        $user->update($data);

        ActivityLog::record('user.updated', $user, [
            'email' => $user->email,
            'old_role' => $oldRole,
            'new_role' => $user->role,
            'old_active' => $oldActive,
            'new_active' => $user->is_active,
        ]);

        return redirect()
            ->route('security.index')
            ->with('success', 'Benutzer wurde aktualisiert.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('security.index')
                ->with('error', 'Du kannst deinen eigenen Account nicht löschen.');
        }

        if ($this->isLastActiveManager($user)) {
            return redirect()
                ->route('security.index')
                ->with('error', 'Der letzte aktive Manager darf nicht gelöscht werden.');
        }

        $email = $user->email;
        $role = $user->role;

        ActivityLog::record('user.deleted', $user, [
            'email' => $email,
            'role' => $role,
        ]);

        $user->delete();

        return redirect()
            ->route('security.index')
            ->with('success', 'Benutzer wurde gelöscht.');
    }

    private function isLastActiveManager(User $user): bool
    {
        if ($user->role !== User::ROLE_MANAGER) {
            return false;
        }

        return User::query()
            ->where('role', User::ROLE_MANAGER)
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count() === 0;
    }

    private function roles(): array
    {
        return [
            User::ROLE_MANAGER => 'Manager / Chef',
            User::ROLE_WHOLESALE => 'Großhandel / Wholesale',
            User::ROLE_WAREHOUSE => 'Lager / Warehouse',
        ];
    }
}
