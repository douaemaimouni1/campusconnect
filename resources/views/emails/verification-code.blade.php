<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Code de vérification</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#4f46e5; padding:24px; text-align:center;">
                            <span style="color:#ffffff; font-size:20px; font-weight:bold;">CampusConnect</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:15px; color:#111827; margin:0 0 12px;">Votre code de vérification</p>
                            <p style="font-size:14px; color:#6b7280; margin:0 0 24px;">
                                Utilisez le code ci-dessous pour confirmer votre adresse email. Ce code expire dans 10 minutes.
                            </p>
                            <div style="background-color:#f3f4f6; border-radius:12px; padding:20px; text-align:center; margin-bottom:24px;">
                                <span style="font-size:32px; font-weight:bold; letter-spacing:6px; color:#4f46e5;">
                                    {{ $code }}
                                </span>
                            </div>
                            <p style="font-size:13px; color:#9ca3af; margin:0;">
                                Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px; text-align:center; background-color:#f9fafb;">
                            <span style="font-size:12px; color:#9ca3af;">CampusConnect — Plateforme universitaire</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>