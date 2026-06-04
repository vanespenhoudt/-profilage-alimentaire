@extends(($clientView ?? false) ? 'layouts.public' : 'layouts.app')

@section('title', 'Bilan – ' . $client->nom_complet)

@section('content')
@php
use App\Data\QuestionnaireData;
$scores  = $questionnaire->scores ?? [];
$notes   = $questionnaire->interpretation_notes ?? [];
$answers = $questionnaire->answers ?? [];

/* ── Tips Julia Ross (jr1→jr8) ───────────────────────────────── */
$jrTips = [
    'jr1' => [
        'label' => 'Classe 1 – Neuromédiateurs',
        'tips'  => [
            'Possible déficit en neurotransmetteurs impliqués dans le sommeil, l\'humeur et la gestion du stress.',
            'Priorités : protéines de qualité, bonnes graisses, sommeil.',
        ],
    ],
    'jr2' => [
        'label' => 'Classe 2 – Régimes',
        'tips'  => [
            'Historique probable de restrictions alimentaires répétées.',
            'Priorités : retrouver la satiété, abandonner la logique de régime.',
        ],
    ],
    'jr3' => [
        'label' => 'Classe 3 – Glycémie',
        'tips'  => [
            'Glycémie instable pouvant provoquer fatigue, fringales et variations d\'humeur.',
            'Priorités : protéines à chaque repas, réduction des sucres rapides, repas réguliers.',
            'Cure suggérée : Décrochez des sucres.',
        ],
    ],
    'jr4' => [
        'label' => 'Classe 4 – Thyroïde',
        'tips'  => [
            'Terrain évoquant un ralentissement du métabolisme.',
            'Priorités : soutien thyroïdien, gestion du stress, alimentation nourrissante.',
        ],
    ],
    'jr5' => [
        'label' => 'Classe 5 – Allergies',
        'tips'  => [
            'Terrain réactif pouvant être associé à des sensibilités alimentaires ou environnementales.',
            'Priorités : rechercher les déclencheurs, diminuer la charge inflammatoire.',
        ],
    ],
    'jr6' => [
        'label' => 'Classe 6 – Hormones',
        'tips'  => [
            'Possible déséquilibre hormonal.',
            'Priorités : sommeil, gestion du stress, soutien hormonal.',
        ],
    ],
    'jr7' => [
        'label' => 'Classe 7 – Intestins',
        'tips'  => [
            'Terrain digestif fragilisé.',
            'Priorités : flore intestinale, digestion, inflammation.',
            'Cure suggérée : Nouvelle Flore.',
        ],
    ],
    'jr8' => [
        'label' => 'Classe 8 – Carences en acides gras essentiels',
        'tips'  => [
            'Apports insuffisants ou mauvaise utilisation des bonnes graisses.',
            'Priorités : huiles vierges, poissons gras, œufs, avocat.',
        ],
    ],
];

/* ── Sous-profils neuromédiateurs (affichés quand jr1 dépassé) ── */
$jrSubProfiles = [
    ['label' => 'Sérotonine', 'tips' => [
        'Carence probable en sérotonine.',
        'Signes fréquents : anxiété, ruminations, perfectionnisme, difficultés d\'endormissement, envies de glucides en soirée.',
        'Priorités : sommeil, protéines, gestion du stress.',
    ]],
    ['label' => 'GABA', 'tips' => [
        'Système de relaxation insuffisamment actif.',
        'Signes fréquents : tension, nervosité, hypervigilance.',
        'Priorités : ralentir, restaurer le système nerveux.',
    ]],
    ['label' => 'Dopamine', 'tips' => [
        'Possible déficit en dopamine.',
        'Signes fréquents : manque de motivation, procrastination, fatigue mentale.',
        'Priorités : protéines, activité physique, objectifs stimulants.',
    ]],
    ['label' => 'Endorphines', 'tips' => [
        'Possible déficit en endorphines.',
        'Signes fréquents : hypersensibilité émotionnelle, besoin de réconfort alimentaire.',
        'Priorités : stabilité glycémique, soutien émotionnel.',
    ]],
];

/* ── Tips Métaboltyping ───────────────────────────────────────── */
$metTips = [
    'Chasseur B' => [
        ['section' => 'Interprétation', 'items' => [
            'Votre métabolisme fonctionne généralement mieux avec une proportion plus importante de protéines et de graisses naturelles.',
        ]],
        ['section' => 'Aliments généralement favorables', 'items' => [
            'Œufs', 'Viandes', 'Poissons', 'Volailles', 'Avocat', 'Olives', 'Huiles vierges', 'Légumes',
        ]],
        ['section' => 'À surveiller', 'items' => [
            'Excès de céréales', 'Sucres rapides', 'Jus de fruits', 'Repas très riches en glucides',
        ]],
        ['section' => 'Conseil principal', 'items' => [
            'Chaque repas devrait contenir une source de protéines.',
        ]],
    ],
    'Cueilleur A' => [
        ['section' => 'Interprétation', 'items' => [
            'Votre métabolisme est souvent mieux adapté à une alimentation comprenant davantage de glucides complexes.',
        ]],
        ['section' => 'Aliments généralement favorables', 'items' => [
            'Riz', 'Quinoa', 'Sarrasin', 'Légumineuses', 'Légumes', 'Fruits selon tolérance',
        ]],
        ['section' => 'À surveiller', 'items' => [
            'Excès de protéines animales', 'Excès de graisses',
        ]],
        ['section' => 'Conseil principal', 'items' => [
            'Maintenir une alimentation variée et riche en végétaux.',
        ]],
    ],
    'Mixte' => [
        ['section' => 'Interprétation', 'items' => [
            'Votre métabolisme semble apprécier un équilibre entre protéines, glucides et bonnes graisses.',
        ]],
        ['section' => 'Conseil principal', 'items' => [
            'Éviter les extrêmes alimentaires.',
        ]],
    ],
];

/* ── Tips Ayurveda ────────────────────────────────────────────── */
$ayTips = [
    'Vâta'  => [
        ['section' => 'Interprétation', 'items' => [
            'Terrain sensible au stress, au froid et à l\'irrégularité.',
        ]],
        ['section' => 'Priorités', 'items' => [
            'Repas réguliers', 'Aliments cuits', 'Chaleur', 'Bonnes graisses',
        ]],
        ['section' => 'À limiter', 'items' => [
            'Jeûnes', 'Crudités excessives', 'Repas sautés',
        ]],
    ],
    'Pitta' => [
        ['section' => 'Interprétation', 'items' => [
            'Terrain intense, volontaire, avec un métabolisme souvent puissant.',
        ]],
        ['section' => 'Priorités', 'items' => [
            'Modération', 'Hydratation', 'Aliments rafraîchissants',
        ]],
        ['section' => 'À limiter', 'items' => [
            'Alcool', 'Aliments très épicés', 'Excitants',
        ]],
    ],
    'Kapha' => [
        ['section' => 'Interprétation', 'items' => [
            'Terrain stable avec tendance au ralentissement métabolique.',
        ]],
        ['section' => 'Priorités', 'items' => [
            'Mouvement', 'Alimentation légère', 'Épices digestives',
        ]],
        ['section' => 'À limiter', 'items' => [
            'Excès de sucres', 'Excès de laitages', 'Sédentarité',
        ]],
    ],
];

/* ── Tips Diathèse ────────────────────────────────────────────── */
$diathTips = [
    1 => [
        ['section' => 'Terrain robuste', 'items' => [
            'Bonne énergie.',
            'Bonne résistance au stress.',
            'Récupération rapide.',
            'Bonne tolérance aux changements alimentaires.',
        ]],
        ['section' => 'Conseils', 'items' => [
            'Peut entreprendre directement une cure ciblée.',
            'Réponse généralement rapide aux changements alimentaires.',
            'Maintenir un mode de vie équilibré.',
        ]],
        ['section' => 'Priorité', 'items' => [
            'Passer directement à l\'interprétation des autres profils.',
        ]],
    ],
    2 => [
        ['section' => 'Terrain fragilisé', 'items' => [
            'Fatigue plus présente.',
            'Récupération plus lente.',
            'Sensibilité accrue au stress.',
            'Fragilité digestive ou nerveuse.',
        ]],
        ['section' => 'Conseils', 'items' => [
            'Renforcer les réserves avant toute cure intensive.',
            'Privilégier le repos.',
            'Augmenter les aliments ressourçants.',
            'Stabiliser les repas.',
        ]],
        ['section' => 'Priorité', 'items' => [
            'Ressourcer avant de corriger.',
        ]],
    ],
];
@endphp

<style>
    /* ── Palette bilan ──────────────────────────────────────────────── */
    /* Variables héritées de variables.css via app.blade.php */

    /* ── Barres de progression ──────────────────────────────────────── */
    .bar-cueilleur { background: var(--color-navy) !important; }
    .bar-chasseur  { background: var(--color-primary) !important; }
    .bar-vata      { background: var(--color-primary) !important; }
    .bar-pitta     { background: var(--color-primary-mid) !important; }
    .bar-kapha     { background: var(--color-primary-dark) !important; }
    .bar-normal    { background: var(--color-primary-dark) !important; }
    .bar-alerte    { background: #dc3545 !important; }

    /* ── Couleurs texte ─────────────────────────────────────────────── */
    .text-cueilleur { color: var(--color-navy) !important; }
    .text-chasseur  { color: var(--color-primary) !important; }
    .text-vata      { color: var(--color-primary) !important; }
    .text-pitta     { color: var(--color-primary-mid) !important; }
    .text-kapha     { color: var(--color-primary-dark) !important; }
    .text-alerte    { color: #dc3545 !important; }
    .text-normal    { color: var(--color-primary-dark) !important; }

    /* ── En-tête de section ─────────────────────────────────────────── */
    .section-header {
        background: var(--color-navy);
        color: var(--color-text-on-green);
        border-radius: var(--radius-card) var(--radius-card) 0 0 !important;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 13px;
    }
    .section-header i { font-size: 1rem; opacity: .9; }

    /* ── Badges types ───────────────────────────────────────────────── */
    .badge-chasseur {
        background: var(--color-primary);
        color: var(--color-navy);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .badge-cueilleur {
        background: var(--color-navy);
        color: var(--color-text-on-green);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .badge-mixte {
        background: var(--color-primary-dark);
        color: var(--color-text-on-green);
        border-radius: var(--radius-pill);
        padding: 6px 18px;
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }

    /* ── Badge Dépassé / Normal ─────────────────────────────────────── */
    .badge-depasse {
        background: #dc3545;
        color: #fff;
        border-radius: 6px;
        padding: 3px 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-normal {
        background: var(--color-primary-dark);
        color: var(--color-text-on-green);
        border-radius: 6px;
        padding: 3px 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }

    /* ── Ligne tableau alerte ───────────────────────────────────────── */
    .row-alerte {
        background: #fff5f5 !important;
    }
    .row-alerte td { color: #dc3545; }

    /* ── Card dosha dominant ────────────────────────────────────────── */
    .card-dosha-dominant {
        border: 2px solid var(--color-primary) !important;
    }

    /* ── Badge dominant ─────────────────────────────────────────────── */
    .badge-dominant {
        background: var(--color-bg-tint);
        color: var(--color-primary-dark);
        border-radius: 8px;
        padding: 3px 12px;
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 600;
    }

    /* ── List group hormone alerte ──────────────────────────────────── */
    .hor-alerte {
        background: var(--color-bg-tint) !important;
        border-color: var(--color-border-tint) !important;
    }

    /* ── Score numéro ───────────────────────────────────────────────── */
    .score-num {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 2rem;
        line-height: 1;
    }

    /* ── En-tête client ─────────────────────────────────────────────── */
    .bilan-header {
        background: var(--color-bg-card);
        border-radius: var(--radius-card);
        padding: 18px 22px;
        box-shadow: none;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .bilan-header .client-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--color-navy), var(--color-primary));
        border-radius: var(--radius-card);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-on-green);
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* ── Barre de progression ───────────────────────────────────────── */
    .progress {
        background: var(--color-border-card);
        border-radius: var(--radius-pill);
    }
    .progress-bar {
        border-radius: var(--radius-pill);
        transition: width .4s ease;
    }

    /* ── Profil résumé Ayurveda ─────────────────────────────────────── */
    .ayurveda-summary {
        background: var(--color-bg-tint);
        border: 1px solid var(--color-border-tint);
        border-radius: var(--radius-card);
        padding: 14px 18px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
    }

    /* ── Boutons du header bilan ────────────────────────────────────── */
    .btn-outline-secondary {
        color: var(--color-navy);
        border: 1.5px solid var(--color-border-light);
        background: transparent;
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 500;
        height: 30px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
    }
    .btn-outline-secondary:hover {
        border-color: var(--color-primary-mid);
        color: var(--color-primary-dark);
        background: transparent;
    }
    .btn-outline-primary {
        color: var(--color-primary-dark);
        border: 1.5px solid var(--color-border-tint);
        background: transparent;
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 500;
        height: 30px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
    }
    .btn-outline-primary:hover {
        background: var(--color-bg-tint);
        border-color: var(--color-border-tint);
        color: var(--color-primary-dark);
    }

    /* ── Tableau ────────────────────────────────────────────────────── */
    .table th {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-text-muted);
        border-bottom: 1px solid var(--color-border-card);
    }
    .table td {
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
        border-color: var(--color-border-card);
        vertical-align: middle;
    }

    /* ── Champs menu ────────────────────────────────────────────────── */
    .form-label {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-text-muted);
        margin-bottom: 4px;
    }
    .form-control {
        border: 1.5px solid var(--color-border-light);
        background: var(--color-bg-input);
        border-radius: var(--radius-input);
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
    }
    .form-control:focus {
        border-color: var(--color-primary-mid);
        box-shadow: 0 0 0 3px var(--color-focus-ring);
        background: var(--color-bg-card);
        color: var(--color-navy);
    }

    /* ════════════════════════════════════════════════════════════════
       ── Encadrés Guide d'interprétation ─────────────────────────────
    ════════════════════════════════════════════════════════════════ */
    .tip-box {
        border-radius: var(--radius-card);
        padding: 14px 16px;
        margin-top: 16px;
        border-left: 3px solid;
    }
    .tip-box--metabol  {
        background: rgba(16,62,61,.05);
        border-color: var(--color-navy);
    }
    .tip-box--ayurveda {
        background: var(--color-bg-tint);
        border-color: var(--color-primary);
    }
    .tip-box--julia {
        background: rgba(59,130,246,.05);
        border-color: #3b82f6;
    }
    .tip-box--diathese {
        background: rgba(124,58,237,.05);
        border-color: #7c3aed;
    }
    .tip-box--sanguin {
        background: rgba(245,158,11,.05);
        border-color: #f59e0b;
    }
    .tip-box--canaris {
        background: rgba(14,165,233,.05);
        border-color: #0ea5e9;
    }
    .canaris-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 20px; padding: 5px 14px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; }
    .canaris-badge--vert   { background: #dcfce7; color: #166534; }
    .canaris-badge--jaune  { background: #fef9c3; color: #854d0e; }
    .canaris-badge--rouge  { background: #fee2e2; color: #991b1b; }
    .canaris-ctx-item { font-size: 13px; color: var(--color-navy); padding: 7px 12px; border-radius: 8px; background: rgba(14,165,233,.06); border-left: 3px solid #0ea5e9; margin-bottom: 6px; }

    .tip-title {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--color-navy);
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }

    .tip-list {
        margin: 0 0 4px 0;
        padding-left: 16px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        color: var(--color-navy);
    }
    .tip-list li { margin-bottom: 3px; line-height: 1.5; }
    .tip-section-title {
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--color-navy);
        opacity: .6;
        margin: 10px 0 4px;
    }
    .tip-section-title:first-child { margin-top: 0; }

    /* Julia Ross : une puce par classe dépassée */
    .tip-jr-block + .tip-jr-block { margin-top: 10px; }
    .tip-jr-label {
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #3b82f6;
        margin-bottom: 4px;
    }

    .tip-separator {
        border: none;
        border-top: 1px solid rgba(0,0,0,.07);
        margin: 12px 0 10px;
    }
    .tip-notes-label {
        font-family: 'Syne', sans-serif;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--color-text-muted);
        margin-bottom: 6px;
    }
    .tip-textarea {
        width: 100%;
        border: 1.5px solid rgba(0,0,0,.08);
        border-radius: 8px;
        padding: 9px 12px;
        font-family: 'Outfit', sans-serif;
        font-size: 12.5px;
        color: var(--color-navy);
        background: rgba(255,255,255,.75);
        resize: vertical;
        min-height: 58px;
        transition: border-color .15s;
    }
    .tip-textarea::placeholder { color: #aaa; font-style: italic; }
    .tip-textarea:focus {
        outline: none;
        border-color: var(--color-primary-mid);
        background: #fff;
    }
</style>

@unless($clientView ?? false)
{{-- Formulaire invisible pour les notes (HTML5 form association) --}}
<form id="notesForm" method="POST"
      action="{{ route('questionnaire.bilan.notes.save', $client) }}"
      style="display:none">
    @csrf
</form>
@endunless

{{-- En-tête page ──────────────────────────────────────────────────── --}}
<div class="bilan-header">
    <div class="client-avatar"><i class="bi bi-person"></i></div>
    <div class="flex-grow-1">
        <h1 class="page-title mb-0">
            <i class="bi bi-bar-chart-line me-2"></i>Bilan nutritionnel
        </h1>
        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
            <span class="bilan-client-chip">{{ $client->nom_complet }}</span>
            <span class="bilan-date">
                <i class="bi bi-clock me-1"></i>Enregistré le {{ $questionnaire->updated_at?->format('d/m/Y à H:i') ?? '—' }}
            </span>
        </div>
    </div>
    @unless($clientView ?? false)
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <a href="{{ route('questionnaire.show', $client) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <button type="submit" form="notesForm" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Enregistrer les notes
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Imprimer
        </button>
    </div>
    @endunless
</div>

{{-- Bandeau session (conseiller uniquement) ────────────────────────── --}}
@unless($clientView ?? false)
<div class="card mb-3">
    <div class="card-body py-2 px-3 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-layers text-muted"></i>
            <span class="small text-muted">Session :</span>
            <span class="fw-semibold small">
                {{ $questionnaire->session_label ?? 'Session initiale' }}
                — {{ $questionnaire->updated_at?->format('d/m/Y') ?? '—' }}
            </span>
        </div>

        @if(isset($allSessions) && $allSessions->count() > 1)
        <form action="{{ route('questionnaire.comparer', $client) }}" method="GET"
              class="d-flex align-items-center gap-2 ms-auto">
            <select name="session_a" class="form-select form-select-sm" style="width:auto">
                @foreach($allSessions as $s)
                <option value="{{ $s->id }}" @selected($s->is_active)>
                    {{ $s->session_label ?? 'Session initiale' }} ({{ $s->updated_at?->format('d/m/Y') }})
                </option>
                @endforeach
            </select>
            <span class="text-muted small">vs</span>
            <select name="session_b" class="form-select form-select-sm" style="width:auto">
                @foreach($allSessions as $s)
                <option value="{{ $s->id }}" @selected(!$s->is_active && $loop->first)>
                    {{ $s->session_label ?? 'Session initiale' }} ({{ $s->updated_at?->format('d/m/Y') }})
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-columns-gap me-1"></i>Comparer
            </button>
        </form>
        @endif

        <form action="{{ route('questionnaire.nouvelle-session', $client) }}" method="POST"
              class="d-flex align-items-center gap-2 {{ isset($allSessions) && $allSessions->count() > 1 ? '' : 'ms-auto' }}">
            @csrf
            <input type="text" name="session_label"
                   class="form-control form-control-sm" style="width:180px"
                   placeholder="Nom de la session (optionnel)">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle session
            </button>
        </form>
    </div>
</div>
@endunless

{{-- Sentinelles (conseiller uniquement) ────────────────────────────── --}}
@unless($clientView ?? false)
@if($client->sentinelles)
<div class="alert d-flex align-items-start gap-2 mb-3 py-2 px-3"
     style="background:#fff8e1;border:1px solid #ffe082;border-radius:10px;">
    <i class="bi bi-exclamation-triangle-fill text-warning mt-1 flex-shrink-0"></i>
    <div>
        <div class="fw-semibold fs-12 text-warning-emphasis mb-1">Alertes / Sentinelles</div>
        <div class="fs-13" style="white-space:pre-wrap;color:var(--color-navy);">{{ $client->sentinelles }}</div>
    </div>
</div>
@endif
@endunless

<div class="row g-3">

    {{-- ════════════════════════════════════════════════════
         CARD 1 — TYPAGE MÉTABOLIQUE
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-activity"></i>
                <span>1. Typage Métabolique</span>
            </div>
            <div class="card-body p-4">
                @php
                    $met   = $scores['metabolique'] ?? ['a' => 0, 'b' => 0, 'type' => 'Mixte'];
                    $total = $met['a'] + $met['b'];
                    $pctA  = $total > 0 ? round(($met['a'] / $total) * 100) : 50;
                    $pctB  = $total > 0 ? round(($met['b'] / $total) * 100) : 50;
                @endphp

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="score-num text-cueilleur">{{ $met['a'] }}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1 fs-13">
                            <span class="text-cueilleur fw-semibold">Cueilleur A ({{ $pctA }}%)</span>
                            <span class="text-chasseur fw-semibold">Chasseur B ({{ $pctB }}%)</span>
                        </div>
                        <div class="progress progress-18">
                            <div class="progress-bar bar-cueilleur" style="width:{{ $pctA }}%;"></div>
                            <div class="progress-bar bar-chasseur"  style="width:{{ $pctB }}%;"></div>
                        </div>
                    </div>
                    <div class="score-num text-chasseur">{{ $met['b'] }}</div>
                </div>

                <div class="text-center">
                    @if($met['type'] === 'Cueilleur A')
                        <span class="badge-cueilleur">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @elseif($met['type'] === 'Chasseur B')
                        <span class="badge-chasseur">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @else
                        <span class="badge-mixte">
                            <i class="bi bi-person-badge me-2"></i>{{ $met['type'] }}
                        </span>
                    @endif
                    <p class="profil-desc">
                        @if($met['type'] === 'Cueilleur A')
                            Profil Cueilleur dominant — régime plutôt végétalien, faible en graisses saturées.
                        @elseif($met['type'] === 'Chasseur B')
                            Profil Chasseur dominant — régime riche en protéines animales et graisses de qualité.
                        @else
                            Profil Mixte — régime équilibré, adapté aux deux tendances.
                        @endif
                    </p>
                </div>

                {{-- Guide d'interprétation Métaboltyping --}}
                @if(isset($metTips[$met['type']]))
                <div class="tip-box tip-box--metabol">
                    <div class="tip-title">
                        <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — {{ $met['type'] }}
                    </div>
                    @foreach($metTips[$met['type']] as $bloc)
                    <div class="tip-section-title">{{ $bloc['section'] }}</div>
                    <ul class="tip-list">
                        @foreach($bloc['items'] as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @endforeach
                    @unless($clientView ?? false)
                    <hr class="tip-separator">
                    <div class="tip-notes-label">Notes du conseiller</div>
                    <textarea name="notes[metabolique]" form="notesForm"
                              class="tip-textarea"
                              placeholder="Ajouter des observations personnalisées...">{{ $notes['metabolique'] ?? '' }}</textarea>
                    @endunless
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 2 — AYURVEDA
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-yin-yang"></i>
                <span>2. Ayurveda</span>
            </div>
            <div class="card-body p-4">
                @php
                    $ay = $scores['ayurveda'] ?? ['vata' => 0, 'pitta' => 0, 'kapha' => 0];
                    $doshas = [
                        ['label' => 'Vâta',  'key' => 'vata',  'max' => 114, 'bar' => 'bar-vata',  'text' => 'text-vata'],
                        ['label' => 'Pitta', 'key' => 'pitta', 'max' => 120, 'bar' => 'bar-pitta', 'text' => 'text-pitta'],
                        ['label' => 'Kapha', 'key' => 'kapha', 'max' => 120, 'bar' => 'bar-kapha', 'text' => 'text-kapha'],
                    ];
                    $maxScore = max($ay['vata'], $ay['pitta'], $ay['kapha']);
                @endphp

                <div class="row g-3">
                    @foreach($doshas as $d)
                    @php
                        $score    = $ay[$d['key']];
                        $pct      = $d['max'] > 0 ? round(($score / $d['max']) * 100) : 0;
                        $dominant = $score === $maxScore && $maxScore > 0;
                    @endphp
                    <div class="col-md-4">
                        <div class="card h-100 {{ $dominant ? 'card-dosha-dominant' : '' }}">
                            <div class="card-body text-center py-4">
                                @if($dominant)
                                <div class="badge-dominant mb-2 d-inline-block">
                                    <i class="bi bi-star-fill me-1"></i>Dominant
                                </div>
                                @endif
                                <div class="fw-bold fs-5 {{ $d['text'] }} font-syne">{{ $d['label'] }}</div>
                                <div class="fw-bold my-2 score-value">{{ $score }}</div>
                                <div class="mb-3 score-pts">/ {{ $d['max'] }} pts</div>
                                <div class="progress mb-2">
                                    <div class="progress-bar {{ $d['bar'] }}" role="progressbar"
                                         style="width:{{ $pct }}%;"></div>
                                </div>
                                <div class="{{ $d['text'] }} score-pct-lbl">{{ $pct }}%</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                    $sorted = collect($doshas)->sortByDesc(fn($d) => $ay[$d['key']]);
                    $dom    = $sorted->first();
                    $sec    = $sorted->skip(1)->first();
                @endphp
                <div class="mt-3 ayurveda-summary">
                    <strong>Profil dominant :</strong>
                    <span class="{{ $dom['text'] }} fw-semibold">{{ $dom['label'] }}</span>
                    — {{ $dom['label'] }} {{ $ay[$dom['key']] }} pts ·
                    <span class="{{ $sec['text'] }} fw-semibold">{{ $sec['label'] }}</span>
                    {{ $ay[$sec['key']] }} pts
                </div>

                {{-- Guide d'interprétation Ayurveda --}}
                @if(isset($ayTips[$dom['label']]))
                <div class="tip-box tip-box--ayurveda">
                    <div class="tip-title">
                        <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — {{ $dom['label'] }}
                    </div>
                    @foreach($ayTips[$dom['label']] as $bloc)
                    <div class="tip-section-title">{{ $bloc['section'] }}</div>
                    <ul class="tip-list">
                        @foreach($bloc['items'] as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @endforeach
                    @unless($clientView ?? false)
                    <hr class="tip-separator">
                    <div class="tip-notes-label">Notes du conseiller</div>
                    <textarea name="notes[ayurveda]" form="notesForm"
                              class="tip-textarea"
                              placeholder="Ajouter des observations personnalisées...">{{ $notes['ayurveda'] ?? '' }}</textarea>
                    @endunless
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 3 — JULIA ROSS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-brain"></i>
                <span>3. Julia Ross — Classes de déséquilibre</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Classe</th>
                                <th class="text-center col-w-100">Score</th>
                                <th class="text-center col-w-100">Seuil</th>
                                <th class="text-center col-w-140">Résultat</th>
                                <th class="col-w-220">Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(QuestionnaireData::$julia_ross as $classe)
                            @php
                                $jr        = ($scores['julia_ross'] ?? [])[$classe['id']] ?? ['total' => 0, 'seuil' => 0, 'depasse' => false];
                                $max       = collect($classe['questions'])->sum('w');
                                $pct       = $max > 0 ? min(100, round(($jr['total'] / $max) * 100)) : 0;
                                $seuil_pct = $max > 0 ? min(100, round(($jr['seuil'] / $max) * 100)) : 0;
                            @endphp
                            <tr class="{{ $jr['depasse'] ? 'row-alerte' : '' }}">
                                <td class="ps-4 py-3">{{ $classe['titre'] }}</td>
                                <td class="text-center fw-bold py-3 fs-13 {{ $jr['depasse'] ? 'text-alerte' : '' }}">
                                    {{ $jr['total'] }}
                                </td>
                                <td class="text-center py-3 text-muted-pa">{{ $jr['seuil'] }}</td>
                                <td class="text-center py-3">
                                    @if($jr['depasse'])
                                        <span class="badge-depasse">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Dépassé
                                        </span>
                                    @else
                                        <span class="badge-normal">
                                            <i class="bi bi-check me-1"></i>Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 pe-4">
                                    <div class="progress">
                                        <div class="progress-bar {{ $jr['depasse'] ? 'bar-alerte' : 'bar-normal' }}"
                                             style="width:{{ $pct }}%;"></div>
                                    </div>
                                    <div class="progress-label-sm">{{ $pct }}% du max</div>
                                </td>
                            </tr>
                            @if($classe['id'] === 'jr3' && !empty($answers['jr_3_4_heures']))
                            <tr>
                                <td colspan="5" class="ps-4 pt-0 pb-2 text-muted small">
                                    <span class="fw-medium">Heures des rages :</span> {{ $answers['jr_3_4_heures'] }}
                                </td>
                            </tr>
                            @endif
                            @if($classe['id'] === 'jr5' && (!empty($answers['jr_5_10_type']) || !empty($answers['jr_5_10_diagnostic'])))
                            <tr>
                                <td colspan="5" class="ps-4 pt-0 pb-2 text-muted small">
                                    @if(!empty($answers['jr_5_10_type']))
                                    <div><span class="fw-medium">Type d'allergie :</span> {{ $answers['jr_5_10_type'] }}</div>
                                    @endif
                                    @if(!empty($answers['jr_5_10_diagnostic']))
                                    <div><span class="fw-medium">Diagnostic :</span> {{ $answers['jr_5_10_diagnostic'] }}</div>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Guide d'interprétation Julia Ross (classes dépassées uniquement) --}}
                @php
                    $jrDepasses = collect(QuestionnaireData::$julia_ross)
                        ->filter(fn($c) => (($scores['julia_ross'] ?? [])[$c['id']]['depasse'] ?? false))
                        ->values();
                @endphp
                @if($jrDepasses->isNotEmpty())
                <div class="p-4 pt-0">
                    <div class="tip-box tip-box--julia">
                        <div class="tip-title">
                            <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — Classes dépassées
                        </div>
                        @foreach($jrDepasses as $classe)
                        @php $tip = $jrTips[$classe['id']] ?? null; @endphp
                        @if($tip)
                        <div class="tip-jr-block">
                            <div class="tip-jr-label">{{ $tip['label'] }}</div>
                            <ul class="tip-list">
                                @foreach($tip['tips'] as $line)
                                <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                            @if($classe['id'] === 'jr1')
                            <div class="tip-section-title" style="color:#3b82f6;margin-top:8px;">Sous-profils neuromédiateurs</div>
                            @foreach($jrSubProfiles as $sub)
                            <div class="tip-jr-block" style="margin-top:6px;padding-left:12px;border-left:2px solid rgba(59,130,246,.3);">
                                <div class="tip-jr-label" style="font-size:10px;">{{ $sub['label'] }}</div>
                                <ul class="tip-list">
                                    @foreach($sub['tips'] as $line)
                                    <li>{{ $line }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        @endif
                        @endforeach
                        @unless($clientView ?? false)
                        <hr class="tip-separator">
                        <div class="tip-notes-label">Notes du conseiller</div>
                        <textarea name="notes[julia_ross]" form="notesForm"
                                  class="tip-textarea"
                                  placeholder="Ajouter des observations personnalisées...">{{ $notes['julia_ross'] ?? '' }}</textarea>
                        @endunless
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 4 — DIATHÈSE DE MÉNÉTRIER
    ════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="section-header">
                <i class="bi bi-diagram-3"></i>
                <span>4. Diathèse de Ménétrier</span>
            </div>
            <div class="card-body p-4">
                @php $di = $scores['diathese'] ?? ['c1_d1' => 0, 'c1_d2' => 0, 'c2_d1' => 0, 'c2_d2' => 0]; @endphp
                <table class="table table-bordered text-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-start"></th>
                            <th class="th-d1">D1</th>
                            <th class="th-d2">D2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-semibold td-label">Enfance (col. 1)</td>
                            <td class="fs-4 fw-bold td-d1">{{ $di['c1_d1'] }}</td>
                            <td class="fs-4 fw-bold td-d2">{{ $di['c1_d2'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-semibold td-label">Adulte (col. 2)</td>
                            <td class="fs-4 fw-bold td-d1">{{ $di['c2_d1'] }}</td>
                            <td class="fs-4 fw-bold td-d2">{{ $di['c2_d2'] }}</td>
                        </tr>
                        @php
                            $totalD1 = $di['c1_d1'] + $di['c2_d1'];
                            $totalD2 = $di['c1_d2'] + $di['c2_d2'];
                        @endphp
                        <tr class="tr-total">
                            <td class="text-start fw-semibold td-label">Total</td>
                            <td class="fs-4 fw-bold td-d1 {{ $totalD1 > $totalD2 ? 'text-green-dark' : '' }}">{{ $totalD1 }}</td>
                            <td class="fs-4 fw-bold td-d2 {{ $totalD2 > $totalD1 ? 'text-green-dark' : '' }}">{{ $totalD2 }}</td>
                        </tr>
                    </tbody>
                </table>
                @php
                    $diagTotal = $totalD1 + $totalD2;
                    $diagPct   = $diagTotal > 0 ? round(($totalD1 / $diagTotal) * 100) : 50;
                @endphp
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1 diag-label-row">
                        <span class="fw-semibold {{ $totalD1 >= $totalD2 ? 'text-green-dark' : 'text-muted-pa' }}">D1 ({{ $diagPct }}%)</span>
                        <span class="fw-semibold {{ $totalD2 > $totalD1 ? 'text-green-dark' : 'text-muted-pa' }}">D2 ({{ 100 - $diagPct }}%)</span>
                    </div>
                    <div class="progress progress-10">
                        <div class="progress-bar bar-cueilleur" style="width:{{ $diagPct }}%;"></div>
                        <div class="progress-bar progress-bar-muted" style="width:{{ 100 - $diagPct }}%;"></div>
                    </div>
                </div>
                <div class="mt-3 diag-tend">
                    @if($totalD1 > $totalD2)
                        Tendance <strong class="text-green-dark">D1</strong> — profil réactif, terrain allergique.
                    @elseif($totalD2 > $totalD1)
                        Tendance <strong class="text-muted-pa">D2</strong> — profil lent, terrain déficitaire.
                    @else
                        Profil <strong class="text-navy">équilibré D1/D2</strong>.
                    @endif
                </div>

                {{-- Guide d'interprétation Diathèse --}}
                @php
                    $col1Total = $di['c1_d1'] + $di['c1_d2'];
                    $col2Total = $di['c2_d1'] + $di['c2_d2'];
                    $d1Dom    = $totalD1 >= $totalD2;
                    $diathNum = $d1Dom ? 1 : 2;
                @endphp
                <div class="tip-box tip-box--diathese">
                    <div class="tip-title">
                        <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — {{ $diathNum === 1 ? 'Terrain robuste' : 'Terrain fragilisé' }}
                    </div>
                    @foreach($diathTips[$diathNum] as $bloc)
                    <div class="tip-section-title">{{ $bloc['section'] }}</div>
                    <ul class="tip-list">
                        @foreach($bloc['items'] as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @endforeach
                    @unless($clientView ?? false)
                    <hr class="tip-separator">
                    <div class="tip-notes-label">Notes du conseiller</div>
                    <textarea name="notes[diathese]" form="notesForm"
                              class="tip-textarea"
                              placeholder="Ajouter des observations personnalisées...">{{ $notes['diathese'] ?? '' }}</textarea>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 5 — BILAN HORMONAL
    ════════════════════════════════════════════════════ --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="section-header">
                <i class="bi bi-droplet-half"></i>
                <span>5. Bilan Hormonal</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach(QuestionnaireData::$hormones as $cat)
                    @php
                        $hor   = ($scores['hormones'] ?? [])[$cat['id']] ?? ['total' => 0, 'max' => 0];
                        $pct   = $hor['max'] > 0 ? round(($hor['total'] / $hor['max']) * 100) : 0;
                        $alert = $pct >= 60;
                    @endphp
                    <li class="list-group-item py-3 px-4 {{ $alert ? 'hor-alerte' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="flex-grow-1 hormones-cat-name">{{ $cat['titre'] }}</span>
                            <span class="fw-bold hormones-cat-pct {{ $alert ? 'text-alerte' : 'text-muted-pa' }}">
                                {{ $hor['total'] }} / {{ $hor['max'] }}
                            </span>
                            @if($alert)
                            <i class="bi bi-exclamation-triangle-fill text-alerte"></i>
                            @endif
                        </div>
                        <div class="progress mt-2 progress-6">
                            <div class="progress-bar {{ $alert ? 'bar-alerte' : 'bar-normal' }}"
                                 role="progressbar" style="width:{{ $pct }}%;"></div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         CARD 6 — CANARIS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        @php
            $can     = $scores['canaris'] ?? ['score' => 0, 'grade' => 'non_canari', 'profil' => 'adulte', 'familles' => ['additifs'], 'contexte' => []];
            $canCtx  = $can['contexte'] ?? [];
        @endphp
        <div class="card">
            <div class="section-header">
                <i class="bi bi-feather"></i>
                <span>6. Canaris</span>
            </div>
            <div class="card-body p-4">

                {{-- Badge grade --}}
                <div class="mb-4">
                    @if($can['grade'] === 'grade_3')
                        <span class="canaris-badge canaris-badge--rouge">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                            Profil canari confirmé
                        </span>
                    @elseif($can['grade'] === 'grade_2')
                        <span class="canaris-badge canaris-badge--rouge">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                            Profil canari probable
                        </span>
                    @elseif($can['grade'] === 'grade_1')
                        <span class="canaris-badge canaris-badge--jaune">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                            Profil canari possible
                        </span>
                    @else
                        <span class="canaris-badge canaris-badge--vert">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                            Pas de profil canari identifié
                        </span>
                    @endif
                </div>

                {{-- Conseils contexte --}}
                @php
                    $ctxConseils = [];
                    if (($canCtx['ctx2'] ?? null) === 'oui')
                        $ctxConseils[] = ['icon' => 'exclamation-triangle-fill', 'text' => 'Plusieurs régimes essayés sans succès : les régimes classiques ne fonctionnent pas sur un terrain canari — piste à confirmer.'];
                    if (($canCtx['ctx3'] ?? null) === 'oui')
                        $ctxConseils[] = ['icon' => 'exclamation-triangle-fill', 'text' => 'Efficacité transitoire des traitements : signe typique d\'hyperréactivité — chaque traitement stimule puis surcharge.'];
                    if (($canCtx['ctx4'] ?? null) === 'oui')
                        $ctxConseils[] = ['icon' => 'exclamation-triangle-fill', 'text' => 'Hypersensibilité médicamenteuse : commencer impérativement par les additifs alimentaires avant tout autre protocole.'];
                    if (($canCtx['ctx5'] ?? null) === 'souvent')
                        $ctxConseils[] = ['icon' => 'info-circle-fill', 'text' => 'Consommation élevée d\'amines biogènes (charcuteries, fromages, fermentés) : piste amines à investiguer.'];
                    if (($canCtx['ctx6'] ?? null) === 'oui')
                        $ctxConseils[] = ['icon' => 'info-circle-fill', 'text' => 'Régime SG-SL > 3 mois : réintroduction délicate, avancer au millimètre avec un thérapeute.'];
                    if (($canCtx['ctx7'] ?? null) !== 'non' && ($canCtx['ctx7'] ?? null) !== null)
                        $ctxConseils[] = ['icon' => 'info-circle-fill', 'text' => 'Cosmétiques ou produits parfumés : commencer par supprimer les parfums et solvants (Tableau 1).'];
                    if (($canCtx['ctx8'] ?? null) === 'plusieurs')
                        $ctxConseils[] = ['icon' => 'info-circle-fill', 'text' => 'Compléments alimentaires multiples : les mettre de côté avant tout test d\'éviction.'];
                @endphp
                @if(count($ctxConseils))
                <div class="mb-3">
                    <div class="fw-semibold fs-13 mb-2" style="color:var(--color-navy);">Points d'attention contexte</div>
                    @foreach($ctxConseils as $conseil)
                    <div class="canaris-ctx-item">
                        <i class="bi bi-{{ $conseil['icon'] }} me-2" style="color:#0ea5e9;"></i>{{ $conseil['text'] }}
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Tip box --}}
                <div class="tip-box tip-box--canaris">
                    <div class="tip-title">
                        <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — Canaris
                    </div>
                    @if(in_array($can['grade'], ['grade_2', 'grade_3']))
                    <div class="tip-section-title">Profil compatible avec un terrain Canari</div>
                    <ul class="tip-list">
                        <li>Allergies</li>
                        <li>Migraines</li>
                        <li>Fatigue chronique</li>
                        <li>Troubles du sommeil</li>
                        <li>Hypersensibilité aux odeurs</li>
                        <li>Troubles digestifs</li>
                        <li>Eczéma</li>
                        <li>Sinusites</li>
                    </ul>
                    <div class="tip-section-title">Priorités</div>
                    <ul class="tip-list">
                        <li>Réduire les additifs</li>
                        <li>Soutenir le foie</li>
                        <li>Rechercher les sensibilités individuelles</li>
                    </ul>
                    <div class="tip-section-title">Cure suggérée</div>
                    <ul class="tip-list">
                        <li>Mes Nerfs en Paix</li>
                    </ul>
                    <hr class="tip-separator">
                    <div class="tip-section-title">Protocole d'éviction — règle fondamentale</div>
                    <ul class="tip-list">
                        <li>Ne pas cumuler les évictions. Commencer par les additifs alimentaires avant d'envisager salicylates ou amines.</li>
                        <li>Tout protocole d'éviction doit être accompagné par un thérapeute.</li>
                    </ul>
                    @elseif($can['grade'] === 'grade_1')
                    <ul class="tip-list">
                        <li>Certaines sensibilités alimentaires ou environnementales peuvent être présentes.</li>
                        <li>Alimentation simple.</li>
                        <li>Réduction des additifs.</li>
                    </ul>
                    @else
                    <ul class="tip-list">
                        <li>Peu d'éléments évoquent une hypersensibilité biochimique importante.</li>
                    </ul>
                    @endif
                    @unless($clientView ?? false)
                    <hr class="tip-separator">
                    <div class="tip-notes-label">Notes du conseiller</div>
                    <textarea name="notes[canaris]" form="notesForm"
                              class="tip-textarea"
                              placeholder="Ajouter des observations personnalisées...">{{ $notes['canaris'] ?? '' }}</textarea>
                    @endunless
                </div>

            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         NOTE PRIORITÉ GROUPE SANGUIN
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="tip-box tip-box--sanguin" style="margin-top:0;">
            <div class="tip-title">
                <i class="bi bi-info-circle-fill"></i>Ordre de priorité d'interprétation
            </div>
            <ul class="tip-list">
                <li>À utiliser comme information secondaire et complémentaire.</li>
                <li><strong>1.</strong> Julia Ross &nbsp;·&nbsp; <strong>2.</strong> Métaboltyping &nbsp;·&nbsp; <strong>3.</strong> Diathèse &nbsp;·&nbsp; <strong>4.</strong> Ayurveda &nbsp;·&nbsp; <strong>5.</strong> Canaris &nbsp;·&nbsp; <strong>6.</strong> Groupe sanguin</li>
            </ul>
            @unless($clientView ?? false)
            <hr class="tip-separator">
            <div class="tip-notes-label">Notes du conseiller</div>
            <textarea name="notes[priorite]" form="notesForm"
                      class="tip-textarea"
                      placeholder="Ajouter des observations générales...">{{ $notes['priorite'] ?? '' }}</textarea>
            @endunless
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         MENU 5 JOURS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-journal-richtext"></i>
                <span>Menu 5 jours</span>
            </div>
            <div class="card-body p-4">

                {{-- Fichier attaché --}}
                @if($questionnaire->menu_file)
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background:var(--color-bg-tint);">
                    <i class="bi bi-file-earmark-text fs-4 text-green-dark"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-13">{{ $questionnaire->menu_file_name }}</div>
                        <div class="fs-12 text-muted-pa">Fichier attaché</div>
                    </div>
                    <a href="{{ Storage::disk('public')->url($questionnaire->menu_file) }}"
                       target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Menu / Plan alimentaire</label>
                    <x-tiptap-editor name="menu_text" :value="$questionnaire->menu_text ?? ''" :readonly="true" />
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         ALIMENTS PRÉFÉRÉS
    ════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="section-header">
                <i class="bi bi-heart"></i>
                <span>10 aliments préférés</span>
            </div>
            <div class="card-body p-4">

                @if(($clientView ?? false) && $questionnaire->aliments_text)
                    <div style="white-space: pre-wrap; font-size: 14px; color: var(--color-navy);">{{ $questionnaire->aliments_text }}</div>

                @elseif(!($clientView ?? false))
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quels sont vos 10 aliments préférés ?</label>
                        <textarea rows="6" class="form-control" readonly
                                  style="background:var(--color-bg-input);resize:none;">{{ $questionnaire->aliments_text ?? '' }}</textarea>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>{{-- /row --}}

@endsection
