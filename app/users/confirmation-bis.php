<?php
session_start();

// UNIQUEMENT pour succès/erreur BDD (pas d'erreurs validation ici)
if (isset($_SESSION['success_message'])) {
    $message_success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message_error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Compatibilité avec anciens paramètres URL
$status = $_GET['status'] ?? '';
if (empty($message_success) && empty($message_error)) {
    if ($status === 'success') {
        $message_success = "Votre message a été envoyé avec succès !";
    } elseif ($status === 'error') {
        $message_error = "Une erreur est survenue lors de l'enregistrement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="shortcut icon" href="../../assets/images/logo_Unizus_photographie.png" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/style/reset.css">
    <link rel="stylesheet" href="../../assets/style/variables.css">
    <link rel="stylesheet" href="../../assets/style/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">


    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .sucess-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="sucess-container">
        <h1>Page de Confirmation</h1>
        <p>Merci de votre visite.</p>
        <a href="../../index.html">Retour à l'accueil</a> <!-- MODIF ICI -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageSuccess = '<?= $message_success ?? '' ?>';
            const messageError = '<?= $message_error ?? '' ?>';


            if (messageSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Message envoyé !',
                    text: messageSuccess,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#4CAF50'
                }).then(() => {
                    window.location.href = '../../index.html'; // page principale
                });

            } else if (messageError) {
                // ERREUR BDD SEULEMENT (pas d'erreurs validation utilisateurs)
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: messageError,
                    confirmButtonText: 'Retour à l\'accueil',
                    confirmButtonColor: '#f44336'
                }).then(() => {
                    window.location.href = '../../index.html#contactUs'; // formulaire
                });
            } else {
                // Si aucun message, redirection simple
                window.location.href = '../../index.html#contactUs'; // formulaire
            }
        });
    </script>
</body>

</html>