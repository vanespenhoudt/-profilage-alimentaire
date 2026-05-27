<x-mail::message>

# Nouveau questionnaire complété

Bonjour {{ $conseiller->name }},

Votre client **{{ $client->nom_complet }}** vient de soumettre son questionnaire nutritionnel.

---

**Date de soumission :** {{ $questionnaire->submitted_at->format('d/m/Y à H:i') }}

@if($client->email)
**Email client :** {{ $client->email }}
@endif

@if($client->tel)
**Téléphone :** {{ $client->tel }}
@endif

---

<x-mail::button :url="route('questionnaire.bilan', $client)">
Voir le bilan
</x-mail::button>

Cordialement,
**Profilage Alimentaire**

</x-mail::message>
