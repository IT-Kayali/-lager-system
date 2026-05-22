<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $action = trim((string) $request->query('action'));
        $entity = trim((string) $request->query('entity'));
        $userId = trim((string) $request->query('user_id'));

        $logs = ActivityLog::query()
            ->with('user')
            ->when($action !== '', fn ($query) => $query->where('action', 'like', "%{$action}%"))
            ->when($entity !== '', fn ($query) => $query->where('entity', $entity))
            ->when($userId !== '', fn ($query) => $query->where('user_id', $userId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $users = User::query()
            ->orderByRaw("FIELD(role, 'manager', 'wholesale', 'warehouse')")
            ->orderBy('name')
            ->get();

        $entities = ActivityLog::query()
            ->whereNotNull('entity')
            ->distinct()
            ->orderBy('entity')
            ->pluck('entity');

        return view('pages.security.index', [
            'logs' => $logs,
            'users' => $users,
            'entities' => $entities,
            'filters' => [
                'action' => $action,
                'entity' => $entity,
                'user_id' => $userId,
            ],
        ]);
    }
}
