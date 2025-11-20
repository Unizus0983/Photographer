<?php
$basePath = '../../'; //pour plus tard si changment dans chemin fichier
$defaultDescription = 'Photographe de mariage et portrait professionnel à Ardes (62). Unizus-art propose des services de photographie personnalisés pour capturer vos moments précieux.';

// Description conditionnelle
$pageDescription = $pageDescription ?? $defaultDescription;
$pageTitle = $pageTitle ?? 'Administration - Unizus Art';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($defaultDescription) ?>">
    <!-- Empêche l'indexation par les moteurs -->
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="googlebot" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="shortcut icon" href="../../assets/images/logo_Unizus_photographie.png" type="image/x-icon">

    <!-- Preconnect critiques (avant tout) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tiny.cloud" crossorigin>

    <!-- Préchargement CSS -->
    <link rel="preload" href="../../assets/style/dashboard-all.css" as="style">
    <link rel="preload"
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,700;1,300&family=Playfair:opsz,wght@14,300;14,400;18,500;18,600;24,700&display=swap"
        as="style">


    <!-- CSS principal -->
    <link rel="stylesheet" href="../../assets/style/dashboard-all.css">

    <!-- Google Fonts -->

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,700;1,300&family=Playfair:opsz,wght@14,300;14,400;18,500;18,600;24,700&display=swap"
        rel="stylesheet">
</head>