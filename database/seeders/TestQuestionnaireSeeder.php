<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Profil fictif : Sophie Noel, 42 ans, femme active.
 * Tendance Chasseur B, profil Pitta-Vata, glycémie instable, hormones déséquilibrées.
 */
class TestQuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('prenom', 'Sophie')->first()
            ?? Client::first();

        if (! $client) {
            $this->command->error('Aucun client trouvé. Lancez d\'abord les seeders de base.');
            return;
        }

        $answers = $this->buildAnswers();
        $scores  = (new QuestionnaireScorer())->calculate($answers);

        Questionnaire::updateOrCreate(
            ['client_id' => $client->id],
            [
                'token'        => Str::random(48),
                'answers'      => $answers,
                'scores'       => $scores,
                'updated_at'   => now(),
                'submitted_at' => now(),
            ]
        );

        $this->command->info("✓ Questionnaire test créé pour : {$client->nom_complet}");
        $this->command->info("  Typage : {$scores['metabolique']['type']} (A:{$scores['metabolique']['a']} B:{$scores['metabolique']['b']})");
        $this->command->info("  Ayurveda : Vata {$scores['ayurveda']['vata']} / Pitta {$scores['ayurveda']['pitta']} / Kapha {$scores['ayurveda']['kapha']}");

        $depassees = collect($scores['julia_ross'])->filter(fn($s) => $s['depasse'])->keys()->join(', ');
        $this->command->info("  Julia Ross dépassées : " . ($depassees ?: 'aucune'));
    }

    private function buildAnswers(): array
    {
        $a = [];

        // ══════════════════════════════════════════════════
        // TYPAGE MÉTABOLIQUE BINAIRE
        // Sophie est plutôt Chasseur B (grand appétit, protéines, dynamique)
        // mais avec quelques tendances Cueilleur
        // ══════════════════════════════════════════════════
        $binaire = [
            'mb1'  => 'b', // Plus jeune
            'mb2'  => 'b', // Chaleureux, amis facilement
            'mb3'  => 'b', // Mieux par temps frais
            'mb4'  => 'b', // Grand appétit
            'mb5'  => 'b', // Digère vite
            'mb6'  => 'b', // Adore manger
            'mb7'  => 'b', // Exprime facilement ses émotions
            'mb8'  => 'b', // Cœur en bandoulière
            'mb9'  => 'b', // Yeux humides
            'mb10' => 'b', // Rosée, rougeurs
            'mb11' => 'b', // Clair, lumineux
            'mb12' => 'b', // Adore les gras
            'mb13' => 'b', // Les gras améliorent son bien-être
            'mb14' => 'a', // Ongles durs (exception)
            'mb15' => 'b', // Irritable sans manger
            'mb16' => 'b', // Gencives foncées/roses
            'mb17' => 'b', // Faim souvent
            'mb18' => 'b', // Réaction forte aux piqûres
            'mb19' => 'b', // Jeûne = se sent mal
            'mb20' => 'b', // Grandes portions
            'mb21' => 'b', // Jus orange → faim/nervosité
            'mb22' => 'b', // Pommes de terre chaque jour
            'mb23' => 'b', // Viande rouge → plus énergique
            'mb24' => 'b', // Beaucoup de salive
            'mb25' => 'b', // Salive aqueuse
            'mb26' => 'b', // Adore le salé
            'mb27' => 'b', // Cicatrise vite
            'mb28' => 'b', // Peau grasse
            'mb29' => 'b', // Doit manger souvent
            'mb30' => 'b', // Souvent envie de collations
            'mb31' => 'b', // Aime beaucoup l'acide
            'mb32' => 'a', // Adore les sucreries (exception)
            'mb33' => 'b', // Repas végétarien ne satisfait pas
            'mb34' => 'b', // Protéines matin → énergisé
            'mb35' => 'b', // Protéines midi → tient jusqu'au soir
            'mb36' => 'b', // Coup de pouce PM : fromage/noix
            'mb37' => 'b', // Extravertie
        ];
        foreach ($binaire as $k => $v) {
            $a[$k] = $v;
        }

        // Symptômes Chasseur B (quelques-uns cochés)
        $a['ms2'] = '1'; // Cicatrices facilement
        $a['ms7'] = '1'; // Gencives saignent
        $a['ms9'] = '1'; // Démangeaisons de peau

        // ══════════════════════════════════════════════════
        // AYURVEDA — Profil Pitta dominant, Vata secondaire
        // Échelle 1–6
        // ══════════════════════════════════════════════════

        // Vata (19 questions)
        $vata = [5, 3, 2, 4, 4, 5, 2, 4, 5, 3, 5, 4, 5, 3, 5, 4, 4, 5, 4];
        foreach ($vata as $i => $val) {
            $a["v{$i}"] = (string) $val;
        }

        // Pitta (20 questions) — dominant
        $pitta = [6, 5, 4, 5, 5, 5, 6, 3, 5, 4, 4, 5, 6, 5, 4, 5, 4, 4, 6, 5];
        foreach ($pitta as $i => $val) {
            $a["p{$i}"] = (string) $val;
        }

        // Kapha (20 questions) — faible
        $kapha = [2, 2, 3, 3, 1, 2, 2, 3, 2, 2, 2, 2, 3, 2, 3, 2, 2, 2, 1, 2];
        foreach ($kapha as $i => $val) {
            $a["k{$i}"] = (string) $val;
        }

        // ══════════════════════════════════════════════════
        // JULIA ROSS — Glycémie instable (jr3) et Thyroïde (jr4)
        // ══════════════════════════════════════════════════

        // jr1 – Chimie du cerveau (seuil 10)
        $a['jr1_0'] = '1'; // Sensible à la douleur        w=4
        $a['jr1_2'] = '1'; // Anxieuse                     w=4  → total 8 (sous seuil)

        // jr2 – Régimes (seuil 12)
        $a['jr2_0'] = '1'; // Obnubilée nourriture         w=4
        $a['jr2_7'] = '1'; // Pense à son poids            w=2
        $a['jr2_9'] = '1'; // Confiance diminuée           w=3  → total 9 (sous seuil)

        // jr3 – Glycémie instable (seuil 15) → DÉPASSÉ
        $a['jr3_0'] = '1'; // Repas somnolents             w=3
        $a['jr3_1'] = '1'; // Irritable, calme après repas w=3
        $a['jr3_2'] = '1'; // Saute repas → vertiges       w=3
        $a['jr3_3'] = '1'; // Rages de sucre               w=4
        $a['jr3_5'] = '1'; // Baisses après café/sucre     w=4
        $a['jr3_8'] = '1'; // Souvent très soif            w=4  → total 21 (DÉPASSÉ)

        // jr4 – Thyroïde (seuil 15) → DÉPASSÉ
        $a['jr4_0'] = '1'; // Peu énergique matin          w=4
        $a['jr4_1'] = '1'; // Frileuse mains/pieds         w=4
        $a['jr4_3'] = '1'; // Poids difficile à perdre     w=4
        $a['jr4_5'] = '1'; // Mal à démarrer le matin      w=4  → total 16 (DÉPASSÉ)

        // jr5 – Allergies (seuil 12)
        $a['jr5_1'] = '1'; // Ballonnée après repas        w=3
        $a['jr5_3'] = '1'; // Gaz fréquents                w=4  → total 7 (sous seuil)

        // jr6 – Hormones femmes (seuil 6) → DÉPASSÉ
        $a['jr6_0'] = '1'; // SPM humeurs/maux de tête     w=4
        $a['jr6_1'] = '1'; // Fringales avant règles       w=4  → total 8 (DÉPASSÉ)

        // jr7 – Parasitose (seuil 13)
        $a['jr7_0'] = '1'; // Gonflée, tensions abdos      w=4
        $a['jr7_2'] = '1'; // Déprime                      w=2  → total 6 (sous seuil)

        // jr8 – Acides gras (seuil 12)
        $a['jr8_0'] = '1'; // Fringales gras               w=4
        $a['jr8_9'] = '1'; // Peau sèche/rugueuse          w=3  → total 7 (sous seuil)

        // ══════════════════════════════════════════════════
        // DIATHÈSE — Tendance D1 enfance, D1/D2 équilibré adulte
        // ══════════════════════════════════════════════════
        $a['d1a'] = 'd1'; // Allergies enfant → D1
        $a['d1b'] = 'd1'; // Mal à aller se coucher ET lever → D1
        $a['d1c'] = 'd1'; // Difficultés endormissement → D1
        $a['d1d'] = 'd2'; // Difficultés attention → D2
        $a['d1e'] = 'd1'; // Optimiste → D1
        $a['d1f'] = 'd1'; // Sports de défi → D1
        $a['d1g'] = 'd1'; // Sport défattigue → D1

        $a['d2a'] = 'd1'; // Récupère vite → D1
        $a['d2b'] = 'd1'; // Foie faible (homéo) → D1
        $a['d2c'] = 'd1'; // Sûre d'elle → D1
        $a['d2d'] = 'd2'; // Préfère effleurements → D2
        $a['d2e'] = 'd1'; // Difficultés lever → D1
        $a['d2f'] = 'd1'; // Sports intensifs → D1
        $a['d2g'] = 'd2'; // Pèse le pour et contre → D2

        // ══════════════════════════════════════════════════
        // BILAN HORMONAL — Progestérone et Cortisol élevés
        // ══════════════════════════════════════════════════

        // h1 – Progestérone (max 10)
        $a['h1_0'] = '1'; // Seins trop gros
        $a['h1_1'] = '1'; // Seins douloureux avant règles
        $a['h1_2'] = '1'; // Ventre gonflé avant règles
        $a['h1_4'] = '1'; // Règles douloureuses
        $a['h1_6'] = '1'; // Irritable avant règles
        $a['h1_8'] = '1'; // Anxieuse avant règles        → 6/10

        // h2 – Cortisol (max 10)
        $a['h2_2'] = '1'; // Stressée
        $a['h2_3'] = '1'; // Cœur bat rapidement
        $a['h2_4'] = '1'; // Coups de fatigue + envie de sucré  → 3/10

        // h3 – Œstradiol (max 11)
        $a['h3_4'] = '1'; // Bouffées de chaleur
        $a['h3_5'] = '1'; // Crises de déprime            → 2/11

        // h4 – Triiodothyronine (max 10)
        $a['h4_0'] = '1'; // Mal lever matin
        $a['h4_2'] = '1'; // Mains/pieds froids
        $a['h4_3'] = '1'; // Frileuse
        $a['h4_8'] = '1'; // Impression de vivre au ralenti → 4/10

        // h5 – DHEA (max 10)
        $a['h5_7'] = '1'; // Manque de libido              → 1/10

        // h6, h7, h8 → rien de coché (normal)

        return $a;
    }
}
