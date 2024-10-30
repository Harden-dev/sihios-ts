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

    <p> Nous vous remercions pour l'intérêt que vous portez à SIHIOTS. Cependant, après examen de votre demande, nous
        sommes au regret de vous informer que votre adhésion à l'espace membre n'a pas été approuvée.</p>
    <p>Si vous avez des questions ou souhaitez des précisions concernant ce refus, vous pouvez nous contacter à<a
            href="mailto:sihiotsinfo@gmail.com">cliquer ici</a>.</p>

    <p>Nous restons à votre disposition pour toute demande d'information complémentaire.</p>

    <p>Cordialement,<br>
        Administrateur SIHIOTS</p>

    <hr>

    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
