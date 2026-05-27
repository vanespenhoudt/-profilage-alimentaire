<x-mail::message>

# Votre questionnaire a bien été reçu

Bonjour {{ $client->prenom }},

Nous confirmons la bonne réception de votre questionnaire nutritionnel, soumis le **{{ $questionnaire->submitted_at->format('d/m/Y à H:i') }}**.

Votre conseiller va analyser vos réponses et vous contactera prochainement avec vos résultats et recommandations personnalisées.

Merci pour le temps consacré à remplir ce questionnaire.

---

Cordialement,
**Profilage Alimentaire**

</x-mail::message>
