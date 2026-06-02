<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Questionnaire extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $attributes = [
        'bilan_visible_client' => false,
    ];

    protected $fillable = [
        'client_id',
        'session_label',
        'is_active',
        'token',
        'sections',
        'answers',
        'scores',
        'menu_text',
        'menu_file',
        'menu_file_name',
        'menu_visible_client',
        'bilan_visible_client',
        'aliments_text',
        'aliments_visible_client',
        'interpretation_notes',
        'submitted_at',
        'rgpd_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'sections'               => 'array',
            'answers'                => 'encrypted:array',
            'scores'                 => 'encrypted:array',
            'menu_text'              => 'encrypted',
            'aliments_text'          => 'encrypted',
            'menu_visible_client'    => 'boolean',
            'bilan_visible_client'   => 'boolean',
            'aliments_visible_client' => 'boolean',
            'interpretation_notes'   => 'array',
            'updated_at'             => 'datetime',
            'submitted_at'           => 'datetime',
            'rgpd_accepted_at'       => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->latest();
    }

    // -----------------------------------------------------------------------
    // Merge partiel des réponses
    // Principe : on identifie quelle(s) section(s) sont soumises via les
    // préfixes de clés, puis on écrase UNIQUEMENT ces sections dans les
    // réponses existantes. Les autres sections restent intactes.
    // -----------------------------------------------------------------------

    public static function mergeAnswers(array $existing, array $incoming): array
    {
        // Mapping section → préfixes réels des champs HTML
        $prefixMap = [
            'metabolique'    => ['mb', 'ms'],
            'julia_ross'     => ['jr'],
            'ayurveda'       => ['v', 'p', 'k'],
            'diathese'       => ['d1', 'd2'],
            'hormones'       => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8'],
            'groupe_sanguin' => ['groupe_sanguin'],
            'canaris'        => ['ca', 'ce', 'ctx'],
            'identite'       => ['identite_'],
        ];

        // Détecter les sections présentes dans les nouvelles réponses
        $submitted = [];
        foreach ($prefixMap as $section => $prefixes) {
            foreach ($prefixes as $prefix) {
                foreach (array_keys($incoming) as $key) {
                    if (str_starts_with($key, $prefix)) {
                        $submitted[$section] = $prefixes;
                        continue 3;
                    }
                }
            }
        }

        $merged = $existing;

        foreach ($submitted as $section => $prefixes) {
            // 1. Supprimer les anciennes clés de cette section
            foreach (array_keys($merged) as $key) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        unset($merged[$key]);
                        break;
                    }
                }
            }
            // 2. Ajouter les nouvelles clés de cette section
            foreach ($incoming as $key => $value) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        $merged[$key] = $value;
                        break;
                    }
                }
            }
        }

        return $merged;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function getCompletedQuestionnaires(): array
    {
        $answers  = $this->answers ?? [];
        $keys     = array_keys($answers);
        $sections = [
            'metabolique'    => ['mb', 'ms'],
            'julia_ross'     => ['jr'],
            'ayurveda'       => ['v', 'p', 'k'],
            'diathese'       => ['d1', 'd2'],
            'hormones'       => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8'],
            'groupe_sanguin' => ['groupe_sanguin'],
            'canaris'        => ['ca', 'ce', 'ctx'],
        ];

        $completed = [];
        foreach ($sections as $section => $prefixes) {
            foreach ($keys as $key) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        $completed[] = $section;
                        continue 3;
                    }
                }
            }
        }

        return $completed;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function statusLabel(): string
    {
        if ($this->isSubmitted()) {
            return 'Soumis le ' . $this->submitted_at->format('d/m/Y à H:i');
        }
        if ($this->getRawOriginal('answers')) {
            return 'En cours';
        }
        return 'En attente';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
