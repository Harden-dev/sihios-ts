<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            /* background-color: #f00703;
            color: #ffffff;
            text-decoration: none; */
            border-radius: 5px;
            font-weight: bold;
        }

        a {
            color: #ffffff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Réinitialisation du mot de passe</h1>
        <p>Vous recevez cet e-mail parce que nous avons reçu une demande de réinitialisation du mot de passe de votre
            compte.</p>
        <p>
            <a href="{{ $url }}" class="button" style="background-color: #0385f0; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">Réinitialiser le mot de passe</a>
        </p>
        <p>Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action supplémentaire n'est requise.
        </p>
        <p>Merci,<br>{{ config('app.name') }}</p>
    </div>
</body>

</html>
