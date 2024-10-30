<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informations de connexion</title>

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
    <h2>Bonjour, {{ $user->first_name }}</h2>

    <p> Félicitations, vous avez été désigné en tant qu'administrateur sur la plateforme SIHIOTS. Vous trouverez
        ci-dessous vos informations de connexion pour accéder à votre espace administrateur :</p>
    <p>Identifiant : {{ $user->email }}</p>
    <p>Mot de passe temporaire : {{ $password }}</p>

    <p><strong>Important:</strong>Nous vous recommandons de changer votre mot de passe lors de votre première connexion
        pour garantir la sécurité
        de votre compte. </p>

    <p>Si vous rencontrez des difficultés ou avez besoin d'aide,  n'hésitez pas à nous contacter via notre
        adresse email : <a href="mailto:sihiotsinfo@gmail.com">Cliquer
            ici</a>.</p>
    </p>

    <p>Bienvenue dans l'équipe de SIHIOTS !</p>
    <P>Cordialement</P>

    <hr>
    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>
</body>

</html>
