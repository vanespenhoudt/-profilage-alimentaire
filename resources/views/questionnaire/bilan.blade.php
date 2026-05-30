@extends(($clientView ?? false) ? 'layouts.public' : 'layouts.app')

@section('title', 'Bilan – ' . $client->nom_complet)

@section('content')
@php
use App\Data\QuestionnaireData;
$scores = $questionnaire->scores ?? [];
$notes  = $questionnaire->interpretation_notes ?? [];

/* ── Tips Julia Ross (jr1→jr8) ───────────────────────────────── */
$jrTips = [
    'jr1' => [
        'label' => 'Sérotonine',
        'tips'  => [
            'Carence probable en sérotonine.',
            'Tendance à l\'anxiété, aux ruminations, au perfectionnisme.',
            'Difficultés d\'endormissement. Envie de glucides en soirée.',
            'Priorité : sommeil, gestion du stress, protéines de qualité, éventuellement soutien en tryptophane.',
        ],
    ],
    'jr2' => [
        'label' => 'Catécholamines',
        'tips'  => [
            'Manque de motivation ou d\'énergie mentale. Difficultés de concentration.',
            'Fatigue au réveil. Baisse de l\'élan et de la motivation.',
            'Priorité : protéines suffisantes, gestion du stress chronique.',
        ],
    ],
    'jr3' => [
        'label' => 'GABA',
        'tips'  => [
            'Nervosité, tension, hypervigilance.',
            'Difficulté à se détendre. Sensation d\'être constamment sous pression.',
            'Priorité : ralentir, restaurer le système nerveux.',
        ],
    ],
    'jr4' => [
        'label' => 'Endorphines',
        'tips'  => [
            'Sensibilité émotionnelle. Recherche de réconfort alimentaire.',
            'Difficulté à gérer les émotions.',
            'Priorité : soutien émotionnel et stabilisation glycémique.',
        ],
    ],
    'jr5' => [
        'label' => 'Glycémie',
        'tips'  => [
            'Glycémie instable. Fringales. Coups de pompe.',
            'Irritabilité lorsque les repas sont retardés.',
            'Priorité : protéines et bonnes graisses à chaque repas, réduction des sucres rapides.',
        ],
    ],
    'jr6' => [
        'label' => 'Hormones féminines',
        'tips'  => [
            'Déséquilibre hormonal possible.',
            'Syndrome prémenstruel, périménopause ou ménopause.',
            'Vérifier le statut hormonal et les apports en acides gras essentiels.',
        ],
    ],
    'jr7' => [
        'label' => 'Allergies / hypersensibilités',
        'tips'  => [
            'Terrain inflammatoire ou hypersensible. Intolérances alimentaires possibles.',
            'Envisager une enquête alimentaire ou un carnet des sentinelles.',
        ],
    ],
    'jr8' => [
        'label' => 'Thyroïde',
        'tips'  => [
            'Ralentissement métabolique possible. Fatigue, frilosité, prise de poids.',
            'Vérifier le contexte thyroïdien.',
        ],
    ],
];

/* ── Tips Métaboltyping ───────────────────────────────────────── */
$metTips = [
    'Cueilleur A' => [
        'Tolère mieux les glucides. Peut sauter un repas plus facilement.',
        'Préférence pour les portions plus petites.',
        'Favoriser : légumes, féculents de qualité, protéines modérées.',
    ],
    'Chasseur B' => [
        'Besoin plus élevé en protéines. Supporte mal le jeûne. Faim rapide.',
        'Favoriser : protéines, légumes, bonnes graisses.',
        'Limiter les repas très riches en sucres et féculents seuls.',
    ],
    'Mixte' => [
        'Équilibre entre protéines, lipides et glucides.',
        'Adapter selon les symptômes.',
    ],
];

/* ── Tips Ayurveda ────────────────────────────────────────────── */
$ayTips = [
    'Vâta'  => [
        'Favoriser : chaud, cuit, gras, onctueux, repas réguliers, soupes, mijotés.',
        'Limiter : froid, crudités excessives, jeûne, alimentation sèche, irrégularité des repas.',
    ],
    'Pitta' => [
        'Favoriser : aliments rafraîchissants, légumes verts, douceur, modération.',
        'Limiter : alcool, piments, excès d\'épices, excès de café.',
    ],
    'Kapha' => [
        'Favoriser : léger, épicé modérément, légumes, activité physique.',
        'Limiter : excès de sucres, excès de laitages, excès de féculents, repas trop copieux.',
    ],
];

/* ── Tips Diathèse ────────────────────────────────────────────── */
$diathTips = [
    1 => [
        ['section' => 'Terrain', 'items' => [
            'Bonne vitalité générale.',
            'Forte capacité d\'adaptation.',
            'Réactions vives mais récupération rapide.',
            'Terrain encore dynamique.',
        ]],
        ['section' => 'Conseils', 'items' => [
            'Peut entreprendre directement une cure alimentaire.',
            'Supporte généralement bien les changements alimentaires.',
            'Peut suivre une cure plus intensive si nécessaire.',
            'Prévention avant tout.',
        ]],
        ['section' => 'Objectif', 'items' => [
            'Corriger les excès avant qu\'ils ne s\'installent.',
        ]],
    ],
    2 => [
        ['section' => 'Terrain', 'items' => [
            'Début d\'épuisement des capacités d\'adaptation.',
            'Fatigue plus fréquente.',
            'Récupération moins rapide.',
            'Sensibilité au stress.',
        ]],
        ['section' => 'Conseils', 'items' => [
            'Préparer le terrain avant une cure intensive.',
            'Soutien des minéraux, vitamines et protéines.',
            'Réduction progressive des excitants.',
            'Éviter les cures trop radicales d\'emblée.',
        ]],
        ['section' => 'Objectif', 'items' => [
            'Recharger les batteries avant de mobiliser les capacités d\'élimination.',
        ]],
    ],
    3 => [
        ['section' => 'Terrain', 'items' => ['Fatigue installée.']],
        ['section' => 'Conseils', 'items' => ['Besoin de récupération et de reconstruction.']],
    ],
    4 => [
        ['section' => 'Terrain', 'items' => ['Terrain de blocage chronique.']],
        ['section' => 'Conseils', 'items' => ['Approche progressive et prudente.']],
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
                    <ul class="tip-list">
                        @foreach($metTips[$met['type']] as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
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
                    <ul class="tip-list">
                        @foreach($ayTips[$dom['label']] as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
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
                    $d1Dom     = $totalD1 >= $totalD2;
                    $col1Dom   = $col1Total >= $col2Total;
                    $diathNum  = match(true) {
                        $d1Dom  && $col1Dom  => 1,
                        !$d1Dom && $col1Dom  => 2,
                        $d1Dom  && !$col1Dom => 3,
                        default              => 4,
                    };
                @endphp
                <div class="tip-box tip-box--diathese">
                    <div class="tip-title">
                        <i class="bi bi-lightbulb-fill"></i>Guide d'interprétation — Diathèse {{ $diathNum }}
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
                        <i class="bi bi-exclamation-triangle-fill"></i>Protocole d'éviction — règle fondamentale
                    </div>
                    <ul class="tip-list">
                        <li>Ne pas cumuler les évictions. Commencer par les additifs alimentaires avant d'envisager salicylates ou amines.</li>
                        <li>Tout protocole d'éviction doit être accompagné par un thérapeute.</li>
                    </ul>
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

                <form method="POST" action="{{ route('questionnaire.menu.save', $client) }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Éditeur riche --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Menu / Plan alimentaire</label>
                        <x-tiptap-editor name="menu_text" :value="$questionnaire->menu_text ?? ''" />
                    </div>

                    {{-- Upload fichier --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Joindre un fichier
                            <span class="text-muted-pa fw-normal fs-12 ms-1">(PDF, TXT, DOC, DOCX, JPG — max 10 Mo)</span>
                        </label>
                        <input type="file" name="menu_file" id="menu_file"
                               class="form-control form-control-sm"
                               accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" id="saveMenuBtn">
                        <i class="bi bi-save me-1"></i>Enregistrer le menu
                    </button>
                </form>
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
                    <form method="POST" action="{{ route('questionnaire.aliments.save', $client) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quels sont vos 10 aliments préférés ?</label>
                            <textarea name="aliments_text" rows="6"
                                      class="form-control"
                                      placeholder="Listez les aliments préférés du client, un par ligne...">{{ $questionnaire->aliments_text ?? '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Enregistrer
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

</div>{{-- /row --}}

@vite('resources/js/tiptap-editor.js')

@endsection
