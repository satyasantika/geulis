<?php

namespace Tests\Feature;

use App\Enums\Peran;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
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
        $this->get('/login')->assertRedirect('/masuk');
        $this->get('/admin/login')->assertOk();
        $this->get('/admin/password-reset/request')->assertOk();
    }

    public function test_an_admin_can_sign_in_to_the_panel(): void
    {
        $user = User::factory()->admin()->create();

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

    public function test_a_researcher_can_open_the_panel(): void
    {
        $peneliti = User::factory()->peneliti()->create();

        $this->assertTrue($peneliti->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($peneliti)->get('/admin')->assertOk();
    }

    public function test_students_and_teachers_are_kept_out_of_the_panel(): void
    {
        $siswa = User::factory()->siswa()->create();
        $guru = User::factory()->guru()->create();
        $adminNonaktif = User::factory()->admin()->nonaktif()->create();

        $this->assertFalse($siswa->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($guru->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($adminNonaktif->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($siswa)->get('/admin')->assertForbidden();
        $this->actingAs($guru)->get('/admin')->assertForbidden();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'salah',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_a_signed_in_admin_can_manage_other_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(UserResource::getUrl('index'))->assertOk();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'nama' => 'Operator',
                'username' => 'operator',
                'email' => 'operator@geulis.test',
                'password' => 'password',
                'roles' => [Role::untuk(Peran::Peneliti)->getKey()],
                'aktif' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::query()->where('email', 'operator@geulis.test')->firstOrFail();
        $this->assertTrue($created->punyaPeran(Peran::Peneliti));
        $this->assertTrue($created->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse(UserResource::canDelete($admin));
        $this->assertTrue(UserResource::canDelete($created));
    }
}
