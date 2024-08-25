<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            /* padding: 20px; */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #444;
            font-size: 24px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            font-size: 16px;
            margin-bottom: 10px;
        }
        ul li strong {
            color: #333;
            display: inline-block;
            min-width: 120px;
        }
        h2 {
            font-size: 20px;
            color: #4CAF50;
            margin-top: 20px;
        }
        .message {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            font-size: 16px;
            line-height: 1.5;
            /*white-space: pre-wrap; /* Pour respecter les retours à la ligne dans le message */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nouveau message de contact</h1>
        <p>Vous avez reçu un nouveau message de contact :</p>
        <ul>
            <li><strong>Nom :</strong> {{ $contact->nom_prenom }}</li>
            <li><strong>Email :</strong> {{ $contact->email }}</li>
            <li><strong>Téléphone :</strong> {{ $contact->tel }}</li>
        </ul>
        <h2>Message :</h2>
        <div class="message">
            {{ $contact->message }}
        </div>
    </div>
</body>
</html>
