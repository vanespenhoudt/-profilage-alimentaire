<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_login_page_displays_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Profilage')
                ->assertSee('Connexion')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('.auth-btn');
        });
    }

    public function test_wrong_credentials_shows_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'mauvais@email.com')
                ->type('password', 'mauvais_mdp')
                ->click('.auth-btn')
                ->assertSee('Ces identifiants ne correspondent pas');
        });
    }

    public function test_conseiller_can_login_and_reach_dashboard(): void
    {
        $user = User::factory()->create([
            'email'    => 'conseiller@test.com',
            'password' => bcrypt('password1234'),
            'role'     => Role::Conseiller->value,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password1234')
                ->click('.auth-btn')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'role' => Role::Conseiller->value,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('.btn-topbar-logout')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }
}
