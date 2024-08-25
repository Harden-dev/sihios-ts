<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport quotidien des nouveaux abonnés à la newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Rapport quotidien des nouveaux abonnés à la newsletter </h1>
        <p>Voici la liste des nouveaux abonnés à la newsletter pour la journée du {{ now()->format('d/m/Y') }}
            :</p>

        @if ($subscribers->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Date d'inscription</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $subscriber)
                        <tr>

                            <td>{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p>Nombre total de nouveaux abonnés : {{ $subscribers->count() }}</p>
        @else
            <p>Aucun nouvel abonné pour cette journée.</p>
        @endif

        <div class="footer">
            <p>Ce rapport a été généré automatiquement. Pour plus d'informations, veuillez consulter le tableau de bord
                d'administration.</p>
        </div>
    </div>
</body>

</html>
