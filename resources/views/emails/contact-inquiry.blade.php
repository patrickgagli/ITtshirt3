<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau message de contact – ITtshirt3</title>
    <style>
        body { font-family: Arial, sans-serif; background: #F8FAFC; color: #1E293B; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px;
                   border: 1px solid #E2E8F0; overflow: hidden; }
        .header { background: #0D6EFD; color: white; padding: 32px 40px; }
        .header h1 { margin: 0; font-size: 1.25rem; font-weight: 700; }
        .body { padding: 32px 40px; }
        .field { margin-bottom: 20px; }
        .field-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;
                       color: #64748B; font-weight: 600; margin-bottom: 4px; }
        .field-value { font-size: 0.9375rem; color: #1E293B; }
        .message-box { background: #F8FAFC; border-radius: 8px; padding: 16px 20px;
                       border-left: 4px solid #0D6EFD; white-space: pre-wrap; }
        .footer { padding: 20px 40px; background: #F8FAFC; border-top: 1px solid #E2E8F0;
                  font-size: 0.8125rem; color: #94A3B8; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1><strong>ITtshirt3</strong> — Nouveau message de contact</h1>
        </div>
        <div class="body">
            <div class="field">
                <div class="field-label">Nom</div>
                <div class="field-value">{{ $inquiry['name'] }}</div>
            </div>
            <div class="field">
                <div class="field-label">Adresse e-mail</div>
                <div class="field-value">
                    <a href="mailto:{{ $inquiry['email'] }}" style="color:#0D6EFD;">{{ $inquiry['email'] }}</a>
                </div>
            </div>
            <div class="field">
                <div class="field-label">Sujet</div>
                <div class="field-value">{{ $inquiry['subject'] }}</div>
            </div>
            <div class="field">
                <div class="field-label">Message</div>
                <div class="message-box">{{ $inquiry['message'] }}</div>
            </div>
        </div>
        <div class="footer">
            Ce message a été envoyé depuis le formulaire de contact de
            <a href="{{ config('app.url') }}" style="color:#0D6EFD;">{{ config('app.url') }}</a>
            le {{ now()->translatedFormat('d F Y à H:i') }}.
        </div>
    </div>
</body>
</html>
