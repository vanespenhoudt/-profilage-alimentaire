@extends('layouts.public')

@section('title', 'Politique de confidentialité — Profilage Alimentaire')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                 style="width:64px;height:64px;background:var(--color-bg-tint);">
                <i class="bi bi-shield-lock fs-2 text-green-dark"></i>
            </div>
            <h1 class="h3 fw-bold mb-1 text-navy">Politique de confidentialité</h1>
            <p class="text-muted small mb-0">Profilage Alimentaire &mdash; Version 1.0 &mdash; Juin 2026</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">

                <h2 class="h6 fw-bold text-navy mb-2">1. Responsable du traitement</h2>
                <p class="text-muted-pa fs-13 mb-4">Le responsable du traitement des données collectées via cette application est votre conseiller en nutrition, qui utilise la plateforme Profilage Alimentaire dans le cadre de son activité professionnelle.</p>

                <h2 class="h6 fw-bold text-navy mb-2">2. Données collectées</h2>
                <p class="text-muted-pa fs-13 mb-2">Dans le cadre de votre suivi nutritionnel, les données suivantes sont collectées :</p>
                <p class="fw-semibold fs-13 mb-1">Données d'identification</p>
                <ul class="text-muted-pa fs-13 mb-3">
                    <li>Nom, prénom</li>
                    <li>Âge, sexe, taille, poids</li>
                </ul>
                <p class="fw-semibold fs-13 mb-1">Données de santé (données sensibles au sens du RGPD)</p>
                <ul class="text-muted-pa fs-13 mb-4">
                    <li>Réponses aux questionnaires de profilage métabolique et nutritionnel (Typage Métabolique, Ayurveda, Julia Ross, Diathèse de Ménétrier, Bilan Hormonal, Groupe Sanguin)</li>
                    <li>Scores et résultats calculés à partir de vos réponses</li>
                    <li>Notes et recommandations alimentaires établies par votre conseiller</li>
                </ul>

                <h2 class="h6 fw-bold text-navy mb-2">3. Base légale du traitement</h2>
                <p class="text-muted-pa fs-13 mb-4">Le traitement de vos données de santé est fondé sur votre <strong>consentement explicite</strong>, recueilli lors de votre prise en charge par votre conseiller en nutrition (article 9, paragraphe 2, point a) du RGPD).</p>

                <h2 class="h6 fw-bold text-navy mb-2">4. Finalité du traitement</h2>
                <p class="text-muted-pa fs-13 mb-2">Vos données sont collectées et traitées exclusivement pour :</p>
                <ul class="text-muted-pa fs-13 mb-2">
                    <li>Établir votre profil nutritionnel personnalisé</li>
                    <li>Suivre l'évolution de votre état de santé nutritionnel</li>
                    <li>Formuler des recommandations alimentaires adaptées</li>
                </ul>
                <p class="text-muted-pa fs-13 mb-4">Elles ne sont utilisées à aucune fin commerciale, publicitaire ou statistique.</p>

                <h2 class="h6 fw-bold text-navy mb-2">5. Destinataires des données</h2>
                <p class="text-muted-pa fs-13 mb-2">Vos données sont accessibles uniquement à votre conseiller en nutrition. Elles ne sont ni vendues, ni transmises, ni partagées avec des tiers.</p>
                <p class="text-muted-pa fs-13 mb-4">L'hébergement technique de l'application est assuré par un hébergeur situé dans l'Union européenne, dans le respect du RGPD.</p>

                <h2 class="h6 fw-bold text-navy mb-2">6. Durée de conservation</h2>
                <p class="text-muted-pa fs-13 mb-4">Vos données sont conservées pour la durée de votre suivi nutritionnel, et au maximum <strong>3 ans</strong> après votre dernière consultation. À l'issue de cette période, elles sont supprimées de façon définitive.</p>

                <h2 class="h6 fw-bold text-navy mb-2">7. Sécurité des données</h2>
                <p class="text-muted-pa fs-13 mb-4">Vos données sont protégées par chiffrement AES-256 en base de données. L'accès à l'application est sécurisé par authentification. Seul votre conseiller peut accéder à vos informations.</p>

                <h2 class="h6 fw-bold text-navy mb-2">8. Vos droits</h2>
                <p class="text-muted-pa fs-13 mb-2">Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul class="text-muted-pa fs-13 mb-2">
                    <li><strong>Droit d'accès</strong> : obtenir une copie de vos données personnelles</li>
                    <li><strong>Droit de rectification</strong> : corriger des données inexactes</li>
                    <li><strong>Droit à l'effacement</strong> : demander la suppression de vos données</li>
                    <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format lisible</li>
                    <li><strong>Droit de retrait du consentement</strong> : à tout moment, sans que cela compromette la licéité du traitement antérieur</li>
                </ul>
                <p class="text-muted-pa fs-13 mb-4">Pour exercer ces droits, adressez-vous directement à votre conseiller en nutrition.</p>

                <h2 class="h6 fw-bold text-navy mb-2">9. Réclamation</h2>
                <p class="text-muted-pa fs-13 mb-2">Si vous estimez que le traitement de vos données ne respecte pas la réglementation, vous avez le droit d'introduire une réclamation auprès de l'<strong>Autorité de protection des données (APD)</strong> :</p>
                <ul class="text-muted-pa fs-13 mb-4">
                    <li>Site : <a href="https://www.autoriteprotectiondonnees.be" class="link-green-dark" target="_blank" rel="noopener">www.autoriteprotectiondonnees.be</a></li>
                    <li>Téléphone : +32 2 274 48 00</li>
                </ul>

                <h2 class="h6 fw-bold text-navy mb-2">10. Modifications</h2>
                <p class="text-muted-pa fs-13 mb-0">Cette politique peut être mise à jour. La version en vigueur est toujours accessible depuis l'application.</p>

            </div>
        </div>

    </div>
</div>

@endsection
