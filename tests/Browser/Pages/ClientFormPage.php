<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class ClientFormPage extends Page
{
    public function url(): string
    {
        return '/clients/create';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertPresent('#prenom')
                ->assertPresent('#nom')
                ->assertPresent('#tel')
                ->assertPresent('#rgpd');
    }

    public static function siteElements(): array
    {
        return [
            '@prenom'  => '#prenom',
            '@nom'     => '#nom',
            '@tel'     => '#tel',
            '@email'   => '#email',
            '@adresse' => '#adresse',
            '@bt'      => '#bt',
            '@notes'   => '#notes',
            '@rgpd'    => '#rgpd',
            '@submit'  => 'button[type="submit"]',
        ];
    }

    /**
     * Remplit le formulaire complet de création de client.
     */
    public function fillForm(Browser $browser, array $data): void
    {
        $browser->type('@prenom', $data['prenom'] ?? 'Prénom Test')
                ->type('@nom', $data['nom'] ?? 'Nom Test')
                ->type('@tel', $data['tel'] ?? '+32 400 00 00 00');

        if (!empty($data['email'])) {
            $browser->type('@email', $data['email']);
        }
        if (!empty($data['adresse'])) {
            $browser->type('@adresse', $data['adresse']);
        }
        if (!empty($data['bt'])) {
            $browser->type('@bt', $data['bt']);
        }
        if (!empty($data['notes'])) {
            $browser->type('@notes', $data['notes']);
        }

        $browser->check('@rgpd');
    }

    /**
     * Remplit et soumet le formulaire, attend la redirection vers la fiche client.
     */
    public function createClient(Browser $browser, array $data): void
    {
        $this->fillForm($browser, $data);
        $browser->press('Créer le client')
                ->waitForText($data['prenom'] ?? 'Prénom Test', 10);
    }

    /**
     * Remplit et soumet le formulaire d'édition, attend la confirmation.
     *
     * @param int $clientId
     */
    public function visitEdit(Browser $browser, int $clientId): void
    {
        $browser->visit("/clients/{$clientId}/edit");
    }
}
