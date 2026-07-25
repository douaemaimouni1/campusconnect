<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>CampusConnect</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles

</head>

<body class="bg-gray-100">

    {{ $slot }}

    @livewireScripts

</body>

</html>