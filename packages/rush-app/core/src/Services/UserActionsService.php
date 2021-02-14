<?php

namespace RushApp\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use RushApp\Core\Models\Action;

class UserActionsService
{
    public function canUserPerformAction(Request $request): bool
    {
        $actionName = $request->route()->getActionMethod();
        $controller = $request->route()->getController();
        $entityName = $controller->getBaseModel()->getTable();

        $hasUserAction = $this->getUserActions()
            ->where('action_name', $actionName)
            ->where('entity_name', $entityName)
//            ->where('canBeManagedByOwner', $entityName)
            ->isNotEmpty();

        return $hasUserAction || $controller->getBaseModel()->canBeManagedByOwner;
    }

    public function getUserActions(): Collection
    {
        $userId = Auth::id();
        $cacheTTL = config('boilerplate.user_actions_cache_ttl');
        return Cache::remember("user-actions.$userId", $cacheTTL, function () use ($userId) {
            return Action::query()
                ->join('role_action as ra', 'ra.action_id', '=', 'actions.id')
                ->join('roles as r', 'r.id', '=', 'ra.role_id')
                ->join('user_role as ur', 'ur.role_id', '=', 'r.id')
                ->where('ur.user_id', $userId)
                ->get();
        });
    }
}