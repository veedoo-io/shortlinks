<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ env('APP_NAME', 'Link-cut') }}</title>

    <!-- Styles -->
    <style>
        .center {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 50%;
        }
        .min-vh-100 {
            height: 100vh;
        }
        .align-content-center {
            align-content: center;
        }
        .img {
            width: 256px;
            height: 158px;
        }
    </style>
</head>
<body class="min-vh-100 align-content-center">
    <img src="favicon.ico" alt="favicon" class="center img">
</body>
</html>
