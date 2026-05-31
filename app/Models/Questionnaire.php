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
    ];

    protected function casts(): array
    {
        return [
            'sections'             => 'array',
            'answers'              => 'array',
            'scores'               => 'array',
            'menu_visible_client'   => 'boolean',
            'bilan_visible_client'   => 'boolean',
            'aliments_visible_client' => 'boolean',
            'interpretation_notes'  => 'array',
            'updated_at'           => 'datetime',
            'submitted_at'         => 'datetime',
        ];
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
        if ($this->answers) {
            return 'En cours';
        }
        return 'En attente';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
