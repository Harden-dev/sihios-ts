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
    <p>Votre mot de passe a été réinitialisé comme demandé.</p>
    <p>Votre nouveau mot de passe est : <strong>{{ $newPassword }}</strong></p>
    <p>Pour des raisons de sécurité, nous vous recommandons de changer ce mot de passe dès votre prochaine connexion.
    </p>
    <p>Si vous n'avez pas demandé cette réinitialisation, veuillez contacter immédiatement notre support.</p>
    <hr>
    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
