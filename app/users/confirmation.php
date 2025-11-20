<?php
session_start();

// Récupérer les erreurs de validation
if (isset($_SESSION['form_errors'])) {
    $form_errors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

// UNIQUEMENT pour succès/erreur BDD (pas d'erreurs validation ici)
if (isset($_SESSION['success_message'])) {
    $message_success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message_error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageHeading = "Envoie des demandes visiteurs";
$pageDescription = "Page de confirmation de l'envoie de votre demande pour informations, ou devis";
include '../includes/head.php';
?>

<body>
    <div class="sucess-container">
        <h1>Page de Confirmation</h1>
        <p>Merci de votre visite.</p>
        <a href="../../index.html" class="btn btn-submit">Retour à l'accueil</a>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageSuccess = '<?= $message_success ?? '' ?>';
            const messageError = '<?= $message_error ?? '' ?>';
            const formErrors = <?= isset($form_errors) ? json_encode($form_errors) : '[]' ?>;

            // Afficher les erreurs de validation
            if (formErrors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formulaire incomplet',
                    html: `
                        <div style="text-align: left;">
                            <strong>Veuillez corriger les erreurs suivantes :</strong>
                            <ul style="margin: 1rem 0; padding-left: 1.25rem;">
                                ${formErrors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    `,
                    confirmButtonText: 'Retour au formulaire',
                    confirmButtonColor: '#e84118'
                }).then(() => {
                    window.location.href = '../../index.html#contactUs';
                });

            } else if (messageSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Message envoyé !',
                    text: messageSuccess,
                    confirmButtonText: 'Fermer',
                    confirmButtonColor: '#56f13e'
                }).then(() => {
                    window.location.href = '../../index.html';
                });

            } else if (messageError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: messageError,
                    confirmButtonText: 'Retour à l\'accueil',
                    confirmButtonColor: '#e84118'
                }).then(() => {
                    window.location.href = '../../index.html#contactUs';
                });
            } else {
                window.location.href = '../../index.html#contactUs';
            }
        });
    </script>
</body>

</html>