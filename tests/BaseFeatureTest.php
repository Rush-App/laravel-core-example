<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RushApp\Core\Models\Action;
use RushApp\Core\Models\Language;
use RushApp\Core\Models\Property;
use RushApp\Core\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseFeatureTest extends TestCase
{
    /**
     * @var Language
     */
    protected $currentLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentLanguage = Language::factory()->create();
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

        $property = Property::create(['is_owner' => false]);
        foreach ($this->getBaseActions() as $actionName) {
            $action = Action::create([
                'entity_name' => $entity,
                'action_name' => $actionName,
            ]);

            $role->actions()->attach($action->id, ['property_id' => $property->id]);
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

        $property = Property::create(['is_owner' => $isOwner]);
        $action = Action::create([
            'entity_name' => $entity,
            'action_name' => $actionName,
        ]);

        $role->actions()->attach($action->id, ['property_id' => $property->id]);

        return $this;
    }

    private function getBaseActions(): array
    {
        return config('boilerplate.action_names', []);
    }

    protected function getTranslateTable($entity): string
    {
        return Str::singular($entity).'_translations';
    }
}