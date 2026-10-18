<?php

namespace Tests\Unit;

use App\Models\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientIsHommeTest extends TestCase
{
    public static function valeurs(): array
    {
        return [
            ['M', true], ['m', true], ['H', true], ['Homme', true], [' homme ', true], ['Masculin', true], ['M.', true],
            ['F', false], ['Femme', false], ['', false], [null, false], ['autre', false],
        ];
    }

    #[DataProvider('valeurs')]
    public function test_is_homme(?string $sexe, bool $attendu): void
    {
        $client = new Client();
        $client->setRawAttributes(['sexe' => null]);
        // sexe est chiffré en base : on passe par les réponses du questionnaire
        $this->assertSame($attendu, $client->isHomme(['identite_sexe' => $sexe]));
    }
}
