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
    <h2>Bienvenue, {{ $user->first_name }}</h2>

    <p>
        Nous avons le plaisir de vous informer que votre demande d'adhésion à l'espace membre de SIHIOTS a été acceptée.
        Vous pouvez désormais accéder à votre espace membre et profiter de tous les avantages et services exclusifs
        réservés à nos membres</p>

    <p>Pour vous connecter, veuillez utiliser les identifiants que vous avez créés lors de votre inscription sur notre
        plateforme.</p>

    <p>Si vous avez besoin d'assistance ou de renseignements supplémentaires, n'hésitez pas à nous contacter à <a
            href="mailto:sihiotsinfo@gmail.com">Cliquer ici.</a></p>

    <p>Bienvenue parmi nous !</p>

    <p>
        Cordialement,
        Administrateur SIHIOTS</p>
    <hr>
    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
