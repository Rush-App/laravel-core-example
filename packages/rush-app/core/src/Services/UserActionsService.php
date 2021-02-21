<?php

namespace RushApp\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RushApp\Core\Models\Action;

class UserActionsService
{
    public function canUserPerformAction(Request $request): bool
    {
        return $this->checkModelAction($request);
    }

    protected function checkModelAction(Request $request): bool
    {
        $model = $request->route()->getController()->getBaseModel();
        $actionName = $request->route()->getActionMethod();

        switch ($actionName) {
            case 'index':
                return $model->canIndex();
            case 'show':
                return $model->canShow();
            case 'store':
                return $model->canStore();
            case 'update':
                return $model->canUpdate();
            case 'destroy':
                return $model->canDestroy();
            default:
                return false;
        }
    }

    public function getUserActions(): Collection
    {
        $userId = Auth::id();
        $cacheTTL = config('boilerplate.user_actions_cache_ttl');
        return Cache::remember("user-actions.$userId", $cacheTTL, function () use ($userId) {
            return Action::query()
                ->join('role_action as ra', 'ra.action_id', '=', 'actions.id')
                ->join('properties as p', 'p.id', '=', 'ra.property_id')
                ->join('roles as r', 'r.id', '=', 'ra.role_id')
                ->join('user_role as ur', 'ur.role_id', '=', 'r.id')
                ->where('ur.user_id', $userId)
                ->get();
        });
    }
}