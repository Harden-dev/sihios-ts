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
    <h2>Bonjour, {{ $user->first_name }}</h2>

    <p> Nous souhaitons vous informer que votre compte sur l'espace membre de SIHIOTS a été désactivé. Cela signifie que
        vous ne pourrez plus accéder à votre espace membre ni bénéficier des services et fonctionnalités réservés aux
        membres.</p>
    <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir des informations complémentaires sur cette
        décision, vous pouvez nous contacter via notre adresse email : <a href="mailto:sihiotsinfo@gmail.com">Cliquer
            ici</a>.</p>

    <p>Nous restons à votre disposition pour toute question.</p>

    <p>Cordialement,<br>
        Administrateur SIHIOTS</p>

    <hr>

    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
