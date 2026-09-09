<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_the_filament_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/login')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
        $this->get('/admin/password-reset/request')->assertOk();
    }

    public function test_a_user_can_sign_in_to_the_panel(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'salah',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_a_signed_in_user_can_manage_other_users(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(UserResource::getUrl('index'))->assertOk();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'nama' => 'Operator',
                'username' => 'operator',
                'email' => 'operator@geulis.test',
                'password' => 'password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::query()->where('email', 'operator@geulis.test')->firstOrFail();
        $this->assertTrue($created->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse(UserResource::canDelete($admin));
        $this->assertTrue(UserResource::canDelete($created));
    }
}
