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

    <p> Nous vous remercions de l'intérêt que vous portez à la SIHIOTS et d'avoir rempli notre formulaire d'adhésion sur
        le site..</p>
    <p>Nous souhaitons vous informer que votre demande a bien été reçue et sera traitée dans les meilleurs délais. Une
        fois l'examen terminé, nous vous tiendrons informé(e) des prochaines étapes à suivre pour finaliser votre
        intégration au sein de notre organisation..</p>

    <p>En attendant, n’hésitez pas à nous contacter pour toute question ou précision via notre
        adresse email : <a href="mailto:sihiotsinfo@gmail.com">Cliquer
            ici</a>. Nous serons ravis de vous
        assister..</p>

    <p>Cordialement,<br>
        SIHIOTS </p>

    <hr>

    <footer style="margin-top: 20px; text-align: center; color: #999;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
    </footer>

</body>

</html>
