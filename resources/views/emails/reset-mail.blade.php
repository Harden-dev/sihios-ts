<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Approbation</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h2 {
            color: #2c3e50;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <h2>Bonjour {{ $user->first_name }},</h2>
    <p>Vous avez demandé à réinitialiser votre mot de passe pour accéder à l'espace membre de SIHIOTS. Votre nouveau mot
        de passe temporaire est le suivant :</p>
    <p>Mot de passe temporaire: <strong>{{ $newPassword }}</strong></p>
    <p>Nous vous recommandons de vous connecter dès que possible et de modifier ce mot de passe temporaire pour
        sécuriser votre compte. Pour ce faire, connectez-vous à votre espace membre puis
        rendez-vous dans les paramètres de votre compte pour modifier votre mot de passe
    </p>
    <p>Si vous n'êtes pas à l'origine de cette demande, veuillez contacter notre service d'assistance immédiatement à
        <a href="mailto:sihiotsinfo@gmail.com">Cliquer ici</a>
    </p>

    <p>Merci de votre confiance.<br>

        Cordialement,
    </p>

    <p>Administrateur SIHIOTS</p>
    <hr>
    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
