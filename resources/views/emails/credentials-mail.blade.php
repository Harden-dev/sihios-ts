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
    <h2>Bienvenue, {{ $user->first_name }}</h2>

    <p> Votre compte administrateur sur {{ config('app.name') }} a été créé avec les informations suivantes :</p>
    <p>Email : {{ $user->email }}</p>
    <p>Mot de passe temporaire : {{ $password }}</p>

    <p><strong>Important:</strong> Pour des raisons de sécurité, veuillez vous connecter et modifier votre mot de passe
        avant les 30 minutes suivantes. </p>
</body>

</html>
