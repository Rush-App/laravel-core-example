<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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

    protected function assignAllActionsForAdminUser()
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
                'name' => $actionName,
            ]);

            $role->actions()->attach($action->id, [
                'is_owner' => false,
            ]);
        }

        return $this;
    }

    protected function assignAllActionsForAuthenticatedUser(string $actionName, $isOwner = true)
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => 'User',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->roles()->save($role);

        $action = Action::create([
            'name' => $actionName,
        ]);

        $role->actions()->attach($action->id, [
            'is_owner' => $isOwner,
        ]);

        return $this;
    }

    private function getBaseActions(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map->getName()
            ->filter(fn (string $name) => Str::startsWith($name, ['posts.', 'categories.']))
            ->toArray();
    }

    protected function getTranslateTable($entity): string
    {
        return Str::singular($entity).'_translations';
    }
}