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
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 WHEN 'warehouse' THEN 3 WHEN 'sales' THEN 4 ELSE 99 END")
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
