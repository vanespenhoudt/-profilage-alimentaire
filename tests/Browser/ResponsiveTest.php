<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ResponsiveTest extends DuskTestCase
{
    use DatabaseMigrations;

    private const VIEWPORTS = [
        'iphone-se'  => [375, 667],
        'iphone-14'  => [390, 844],
        'ipad'       => [768, 1024],
        'ipad-pro'   => [1024, 1366],
    ];

    private function hasHorizontalOverflow(Browser $browser): bool
    {
        return (bool) $browser->script(
            'return document.documentElement.scrollWidth > document.documentElement.clientWidth + 1'
        )[0];
    }

    private function screenshotName(string $device, string $page): string
    {
        return "responsive-{$device}-{$page}";
    }

    /** @test */
    public function test_login_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->resize($w, $h)
                    ->visit('/login')
                    ->screenshot($this->screenshotName($device, 'login'))
                    ->assertPresent('input[name="email"]')
                    ->assertPresent('input[name="password"]')
                    ->assertPresent('.auth-btn');

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /login ({$device} {$w}×{$h})"
                );
            }
        });
    }

    /** @test */
    public function test_dashboard_responsive(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        Client::factory()->count(3)->create(['conseiller_id' => $conseiller->id]);

        $this->browse(function (Browser $browser) use ($conseiller) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->loginAs($conseiller)
                    ->resize($w, $h)
                    ->visit('/dashboard')
                    ->screenshot($this->screenshotName($device, 'dashboard'));

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /dashboard ({$device} {$w}×{$h})"
                );
            }
        });
    }

    /** @test */
    public function test_client_show_responsive(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->loginAs($conseiller)
                    ->resize($w, $h)
                    ->visit("/clients/{$client->id}")
                    ->screenshot($this->screenshotName($device, 'client-show'));

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /clients/show ({$device} {$w}×{$h})"
                );
            }
        });
    }

    /** @test */
    public function test_questionnaire_show_responsive(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        Questionnaire::create([
            'client_id' => $client->id,
            'token'     => Str::random(48),
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->loginAs($conseiller)
                    ->resize($w, $h)
                    ->visit("/clients/{$client->id}/questionnaire")
                    ->screenshot($this->screenshotName($device, 'questionnaire-show'));

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /questionnaire/show ({$device} {$w}×{$h})"
                );
            }
        });
    }

    /** @test */
    public function test_bilan_responsive(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        Questionnaire::create([
            'client_id'    => $client->id,
            'token'        => Str::random(48),
            'answers'      => [],
            'scores'       => [],
            'submitted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->loginAs($conseiller)
                    ->resize($w, $h)
                    ->visit("/clients/{$client->id}/bilan")
                    ->screenshot($this->screenshotName($device, 'bilan'));

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /bilan ({$device} {$w}×{$h})"
                );
            }
        });
    }

    /** @test */
    public function test_questionnaire_public_responsive(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);
        Questionnaire::create([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'groupe_sanguin', 'hormones'],
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            foreach (self::VIEWPORTS as $device => [$w, $h]) {
                $browser->resize($w, $h)
                    ->visit("/q/{$token}")
                    ->screenshot($this->screenshotName($device, 'questionnaire-public'));

                $this->assertFalse(
                    $this->hasHorizontalOverflow($browser),
                    "Débordement horizontal sur /q/token ({$device} {$w}×{$h})"
                );
            }
        });
    }
}
