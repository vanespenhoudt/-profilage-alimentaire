@extends('layouts.public')

@section('title', 'Politique de confidentialité — Conseillers — Profilage Alimentaire')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                 style="width:64px;height:64px;background:var(--color-bg-tint);">
                <i class="bi bi-shield-lock fs-2 text-green-dark"></i>
            </div>
            <h1 class="h3 fw-bold mb-1 text-navy">Politique de confidentialité</h1>
            <p class="text-muted small mb-0">Conseillers — Profilage Alimentaire &mdash; Version 1.0 &mdash; Juin 2026</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">

                <h2 class="h6 fw-bold text-navy mb-2">1. Responsable du traitement</h2>
                <p class="text-muted-pa fs-13 mb-4">Le responsable du traitement des données collectées via cette plateforme est l'éditeur de l'application Profilage Alimentaire.</p>

                <h2 class="h6 fw-bold text-navy mb-2">2. Données collectées</h2>
                <p class="text-muted-pa fs-13 mb-2">Dans le cadre de votre utilisation de la plateforme en tant que conseiller en nutrition, les données suivantes sont collectées :</p>
                <p class="fw-semibold fs-13 mb-1">Données d'identification professionnelle</p>
                <ul class="text-muted-pa fs-13 mb-3">
                    <li>Nom, prénom</li>
                    <li>Adresse e-mail professionnelle</li>
                    <li>Numéro de téléphone (optionnel)</li>
                </ul>
                <p class="fw-semibold fs-13 mb-1">Données d'utilisation</p>
                <ul class="text-muted-pa fs-13 mb-4">
                    <li>Identifiants de connexion (e-mail + mot de passe chiffré)</li>
                    <li>Historique des connexions</li>
                    <li>Données saisies dans le cadre du suivi de vos clients</li>
                </ul>

                <h2 class="h6 fw-bold text-navy mb-2">3. Base légale du traitement</h2>
                <p class="text-muted-pa fs-13 mb-4">Le traitement de vos données est fondé sur l'<strong>exécution du contrat</strong> qui vous lie à la plateforme lors de votre inscription (article 6, paragraphe 1, point b) du RGPD).</p>

                <h2 class="h6 fw-bold text-navy mb-2">4. Finalité du traitement</h2>
                <p class="text-muted-pa fs-13 mb-2">Vos données sont collectées et traitées exclusivement pour :</p>
                <ul class="text-muted-pa fs-13 mb-2">
                    <li>Gérer votre accès à la plateforme</li>
                    <li>Vous permettre de suivre vos clients et leurs évaluations nutritionnelles</li>
                    <li>Assurer la sécurité et le bon fonctionnement du service</li>
                </ul>
                <p class="text-muted-pa fs-13 mb-4">Elles ne sont utilisées à aucune fin commerciale, publicitaire ou statistique.</p>

                <h2 class="h6 fw-bold text-navy mb-2">5. Responsabilité en tant que conseiller</h2>
                <p class="text-muted-pa fs-13 mb-2">En utilisant cette plateforme, vous agissez en tant que <strong>responsable du traitement</strong> des données personnelles et de santé de vos clients. À ce titre, vous êtes tenu de :</p>
                <ul class="text-muted-pa fs-13 mb-4">
                    <li>Recueillir le consentement explicite de vos clients avant toute saisie de données</li>
                    <li>Informer vos clients de leurs droits (accès, rectification, effacement)</li>
                    <li>Respecter les obligations du RGPD dans le cadre de votre activité professionnelle</li>
                    <li>Ne pas saisir de données de clients sans relation de suivi établie</li>
                </ul>

                <h2 class="h6 fw-bold text-navy mb-2">6. Destinataires des données</h2>
                <p class="text-muted-pa fs-13 mb-2">Vos données professionnelles sont accessibles uniquement à l'équipe technique de la plateforme, dans le strict cadre de la maintenance et du support. Elles ne sont ni vendues, ni transmises, ni partagées avec des tiers.</p>
                <p class="text-muted-pa fs-13 mb-4">L'hébergement technique de l'application est assuré par un hébergeur situé dans l'Union européenne, dans le respect du RGPD.</p>

                <h2 class="h6 fw-bold text-navy mb-2">7. Durée de conservation</h2>
                <p class="text-muted-pa fs-13 mb-4">Vos données sont conservées pour la durée de votre utilisation active de la plateforme, et au maximum <strong>1 an</strong> après la résiliation de votre accès. À l'issue de cette période, elles sont supprimées de façon définitive.</p>

                <h2 class="h6 fw-bold text-navy mb-2">8. Sécurité des données</h2>
                <p class="text-muted-pa fs-13 mb-4">Vos données et celles de vos clients sont protégées par chiffrement AES-256 en base de données. L'accès à la plateforme est sécurisé par authentification. Les mots de passe sont stockés sous forme hachée et ne sont jamais accessibles en clair.</p>

                <h2 class="h6 fw-bold text-navy mb-2">9. Vos droits</h2>
                <p class="text-muted-pa fs-13 mb-2">Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul class="text-muted-pa fs-13 mb-2">
                    <li><strong>Droit d'accès</strong> : obtenir une copie de vos données personnelles</li>
                    <li><strong>Droit de rectification</strong> : corriger des données inexactes</li>
                    <li><strong>Droit à l'effacement</strong> : demander la suppression de votre compte et de vos données</li>
                    <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format lisible</li>
                    <li><strong>Droit d'opposition</strong> : vous opposer à certains traitements</li>
                </ul>
                <p class="text-muted-pa fs-13 mb-4">Pour exercer ces droits, contactez l'éditeur de la plateforme via l'adresse communiquée lors de votre inscription.</p>

                <h2 class="h6 fw-bold text-navy mb-2">10. Réclamation</h2>
                <p class="text-muted-pa fs-13 mb-2">Si vous estimez que le traitement de vos données ne respecte pas la réglementation, vous avez le droit d'introduire une réclamation auprès de l'autorité de contrôle compétente dans votre pays de résidence.</p>
                <p class="text-muted-pa fs-13 mb-1">En Belgique : <strong>Autorité de protection des données (APD)</strong></p>
                <ul class="text-muted-pa fs-13 mb-4">
                    <li>Site : <a href="https://www.autoriteprotectiondonnees.be" class="link-green-dark" target="_blank" rel="noopener">www.autoriteprotectiondonnees.be</a></li>
                    <li>Téléphone : +32 2 274 48 00</li>
                </ul>

                <h2 class="h6 fw-bold text-navy mb-2">11. Modifications</h2>
                <p class="text-muted-pa fs-13 mb-0">Cette politique peut être mise à jour. La version en vigueur est toujours accessible depuis la plateforme. En cas de modification substantielle, les conseillers en seront informés par e-mail.</p>

            </div>
        </div>

    </div>
</div>

@endsection
