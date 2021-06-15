<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RushApp\Core\Models\Action;
use RushApp\Core\Models\Language;
use RushApp\Core\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseFeatureTest extends TestCase
{
    protected Language $currentLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('cache:clear');
        $this->currentLanguage = Language::query()->first();
    }

    protected function signIn(Authenticatable $user = null, string $guard = null)
    {
        $user = $user ?: User::factory()->create();

        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', "Bearer {$token}");
        parent::actingAs($user);

        return $this;
    }

    protected function assignAllActionsForAdminUser(string $entity)
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => 'Admin',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->roles()->save($role);

        foreach ($this->getBaseActions() as $actionName) {
            $action = Action::create([
                'entity_name' => $entity,
                'action_name' => $actionName,
            ]);

            $role->actions()->attach($action->id, [
                'is_owner' => false,
            ]);
        }

        return $this;
    }

    protected function assignAllActionsForAuthenticatedUser(string $entity, string $actionName, $isOwner = true)
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => 'User',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->roles()->save($role);

        $action = Action::create([
            'entity_name' => $entity,
            'action_name' => $actionName,
        ]);

        $role->actions()->attach($action->id, [
            'is_owner' => $isOwner,
        ]);

        return $this;
    }

    private function getBaseActions(): array
    {
        return config('rushapp_core.action_names', []);
    }

    protected function getTranslateTable($entity): string
    {
        return Str::singular($entity).'_translations';
    }
}