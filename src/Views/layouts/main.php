<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Burger Restaurant' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background-color: #2d2d2d !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.3);
        }
        .navbar-brand {
            color: #ffc107 !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #fff !important;
            transition: color 0.3s;
        }
        .nav-link:hover {
            color: #ffc107 !important;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
            color: #000;
            font-weight: bold;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .card {
            background-color: #2d2d2d;
            border: 1px solid #3d3d3d;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background-color: #2d2d2d;
            padding: 20px 0;
            margin-top: 50px;
            text-align: center;
            color: #999;
        }
    </style>
    <?= $additionalStyles ?? '' ?>
</head>
<body>
    <?= $content ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $additionalScripts ?? '' ?>
</body>
</html>
