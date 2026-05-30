<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lien d'invitation invalide — Profilage Alimentaire</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f4f4f4; font-family: 'Helvetica Neue', Arial, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.08); max-width: 440px; width: 100%; overflow: hidden; }
        .header { background: #103e3d; padding: 32px 40px 24px; text-align: center; }
        .header h1 { color: #fff; font-size: 18px; font-weight: 700; }
        .icon-wrap { width: 56px; height: 56px; background: rgba(255,255,255,.12); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .body { padding: 36px 40px; text-align: center; }
        .error-icon { font-size: 48px; margin-bottom: 16px; }
        h2 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 10px; }
        p { font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: #103e3d; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 600; }
        .footer { background: #f9fafb; border-top: 1px solid #f0f0f0; padding: 16px 40px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="icon-wrap">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="#5ee5c8"/>
            </svg>
        </div>
        <h1>Profilage Alimentaire</h1>
    </div>

    <div class="body">
        @if($reason === 'used')
            <div class="error-icon">✅</div>
            <h2>Invitation déjà utilisée</h2>
            <p>Ce lien d'invitation a déjà été utilisé pour créer un compte.<br>Si c'est vous, connectez-vous directement.</p>
        @elseif($reason === 'expired')
            <div class="error-icon">⏰</div>
            <h2>Invitation expirée</h2>
            <p>Ce lien d'invitation n'est plus valide.<br>Contactez la personne qui vous a invité pour qu'elle vous envoie un nouveau lien.</p>
        @else
            <div class="error-icon">🔗</div>
            <h2>Lien invalide</h2>
            <p>Ce lien d'invitation est introuvable ou incorrect.<br>Vérifiez que vous avez copié l'URL complète depuis l'email.</p>
        @endif

        <a href="{{ route('login') }}" class="btn">Aller à la page de connexion</a>
    </div>

    <div class="footer">
        <p>Profilage Alimentaire · mve-nutrition.be</p>
    </div>
</div>
</body>
</html>
