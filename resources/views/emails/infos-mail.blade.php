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

    <p> Nous vous confirmons que nous avons bien reçu votre demande d'adhésion. Celle-ci est actuellement en cours de
        validation par notre équipe.</p>
    <p>Nous vous informerons dès que le processus d'examen sera finalisé.</p>

    <p>Nous vous remercions pour votre confiance et restons à votre disposition pour toute question complémentaire.</p>

    <p>Cordialement,<br>
       SIHIOTS - <a href="mailto:sihiotsinfo@gmail.com">Contacter via mail</a>.</p>

    <hr>

    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
