<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RushApp\Core\Models\Language;
use RushApp\Core\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseTest extends TestCase
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

    protected function assignAllActionsForAuthenticatedUser(string $entity)
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => 'Admin',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->roles()->save($role);

        foreach ($this->getBaseActions() as $actionName) {
            $role->actions()->create([
                'entity_name' => $entity,
                'action_name' => $actionName,
            ]);
        }

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