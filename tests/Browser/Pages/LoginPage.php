<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class LoginPage extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('.auth-btn');
    }

    public static function siteElements(): array
    {
        return [
            '@email'    => 'input[name="email"]',
            '@password' => 'input[name="password"]',
            '@submit'   => '.auth-btn',
        ];
    }

    /**
     * Remplit les champs et soumet le formulaire de connexion.
     */
    public function loginWith(Browser $browser, string $email, string $password): void
    {
        $browser->type('@email', $email)
                ->type('@password', $password)
                ->click('@submit');
    }

    /**
     * Connexion complète : remplit, soumet et attend la redirection vers le dashboard.
     */
    public function loginAndWaitForDashboard(Browser $browser, string $email, string $password): void
    {
        $this->loginWith($browser, $email, $password);
        $browser->waitForLocation('/dashboard', 10);
    }
}
