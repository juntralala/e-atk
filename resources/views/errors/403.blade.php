<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .container {
            text-align: center;
            padding: 2rem;
        }

        .error-code {
            font-size: 180px;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 1rem;
            color: #fff;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .error-message {
            font-size: 1.5rem;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">403</div>
        <p class="error-message">Stop! Akses ditolak</p>
    </div>
</body>
</html>