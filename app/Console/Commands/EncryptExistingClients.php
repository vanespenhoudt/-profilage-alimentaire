<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptExistingClients extends Command
{
    protected $signature   = 'clients:encrypt-existing';
    protected $description = 'Chiffre (AES-256) les données personnelles existantes en base — idempotent';

    private array $clientFields = [
        'prenom', 'nom', 'tel', 'email',
        'adresse', 'bt', 'notes', 'sexe', 'sentinelles',
        'age', 'taille', 'poids',
    ];

    private array $questionnaireStringFields = ['menu_text', 'aliments_text'];

    private array $questionnaireJsonFields = ['answers', 'scores'];

    public function handle(): int
    {
        $this->encryptClients();
        $this->encryptQuestionnaires();

        $this->info('Chiffrement terminé.');

        return self::SUCCESS;
    }

    private function encryptClients(): void
    {
        $total = 0;

        DB::table('clients')->orderBy('id')->each(function (object $row) use (&$total): void {
            $updates = [];

            foreach ($this->clientFields as $field) {
                $value = $row->{$field} ?? null;
                if ($value !== null && !$this->isEncrypted($value)) {
                    $updates[$field] = Crypt::encryptString((string) $value);
                }
            }

            if (!empty($updates)) {
                DB::table('clients')->where('id', $row->id)->update($updates);
                $total++;
            }
        });

        $this->line("  clients mis à jour : {$total}");
    }

    private function encryptQuestionnaires(): void
    {
        $total = 0;

        DB::table('questionnaires')->orderBy('id')->each(function (object $row) use (&$total): void {
            $updates = [];

            foreach ($this->questionnaireStringFields as $field) {
                $value = $row->{$field} ?? null;
                if ($value !== null && !$this->isEncrypted($value)) {
                    $updates[$field] = Crypt::encryptString((string) $value);
                }
            }

            foreach ($this->questionnaireJsonFields as $field) {
                $value = $row->{$field} ?? null;
                if ($value !== null && !$this->isEncrypted($value)) {
                    // Value is a raw JSON string — encrypt as-is (encrypted:array expects encrypted JSON)
                    $updates[$field] = Crypt::encryptString((string) $value);
                }
            }

            if (!empty($updates)) {
                DB::table('questionnaires')->where('id', $row->id)->update($updates);
                $total++;
            }
        });

        $this->line("  questionnaires mis à jour : {$total}");
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException) {
            return false;
        }
    }
}
