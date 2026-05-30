<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Profilage Alimentaire</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f4; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #103e3d; padding: 32px 40px; text-align: center; }
        .header-icon { width: 48px; height: 48px; background: rgba(255,255,255,.15); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; margin: 0; letter-spacing: -.3px; }
        .body { padding: 36px 40px; }
        .body p { font-size: 15px; line-height: 1.6; color: #374151; margin: 0 0 16px; }
        .inviter { display: inline-block; background: #f0faf9; border: 1px solid #d1ede9; border-radius: 8px; padding: 10px 16px; font-size: 14px; color: #103e3d; font-weight: 600; margin-bottom: 24px; }
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #103e3d; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-size: 15px; font-weight: 700; letter-spacing: .3px; }
        .link-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; word-break: break-all; font-size: 12px; color: #6b7280; font-family: monospace; margin: 16px 0 24px; }
        .expiry { font-size: 13px; color: #9ca3af; text-align: center; margin: 0 0 8px; }
        .footer { background: #f9fafb; border-top: 1px solid #f0f0f0; padding: 20px 40px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 0; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="header-icon">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" fill="#5ee5c8"/>
            </svg>
        </div>
        <h1>Profilage Alimentaire</h1>
    </div>

    <div class="body">
        <p>Bonjour,</p>

        <p>Vous avez été invité à rejoindre la plateforme <strong>Profilage Alimentaire</strong> en tant que conseiller.</p>

        @if($invitation->invitedBy)
        <div class="inviter">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            Invité par {{ $invitation->invitedBy->name }}
        </div>
        @endif

        <p>Cliquez sur le bouton ci-dessous pour créer votre compte. Ce lien est <strong>valable 7 jours</strong>.</p>

        <div class="btn-wrap">
            <a href="{{ route('invitation.show', $invitation->token) }}" class="btn">
                Créer mon compte
            </a>
        </div>

        <p style="font-size:13px;color:#6b7280;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :</p>
        <div class="link-box">{{ route('invitation.show', $invitation->token) }}</div>

        <p class="expiry">
            ⏳ Lien valable jusqu'au {{ $invitation->expires_at->format('d/m/Y à H:i') }}
        </p>

        <p style="font-size:13px;color:#9ca3af;">Si vous n'attendiez pas cette invitation, vous pouvez ignorer ce message.</p>
    </div>

    <div class="footer">
        <p>Profilage Alimentaire · mve-nutrition.be</p>
    </div>

</div>
</body>
</html>
