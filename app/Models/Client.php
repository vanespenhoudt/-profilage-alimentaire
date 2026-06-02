<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'conseiller_id',
        'prenom',
        'nom',
        'age',
        'sexe',
        'taille',
        'poids',
        'sentinelles',
        'tel',
        'email',
        'adresse',
        'bt',
        'rgpd',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'prenom'     => 'encrypted',
            'nom'        => 'encrypted',
            'tel'        => 'encrypted',
            'email'      => 'encrypted',
            'adresse'    => 'encrypted',
            'bt'         => 'encrypted',
            'notes'      => 'encrypted',
            'sexe'       => 'encrypted',
            'sentinelles' => 'encrypted',
            'age'        => 'encrypted',
            'taille'     => 'encrypted',
            'poids'      => 'encrypted',
            'rgpd'       => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Client $client): void {
            if (empty($client->code)) {
                do {
                    $code = 'CLI-' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
                } while (static::where('code', $code)->exists());

                $client->code = $code;
            }
        });
    }

    public function conseiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conseiller_id');
    }

    public function questionnaire(): HasOne
    {
        return $this->hasOne(Questionnaire::class);
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
