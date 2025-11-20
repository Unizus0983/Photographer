<?php
require_once '../includes/connect.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $objet_demande = trim($_POST['objet_demande'] ?? '');
    $date_evenement = $_POST['date_evenement'] ?? null;
    $message = trim($_POST['message'] ?? '');

    // Validation des champs obligatoires
    if (empty($nom)) $errors[] = "Le nom est obligatoire.";
    if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";
    if (empty($email)) $errors[] = "L'email est obligatoire.";
    if (empty($objet_demande)) $errors[] = "L'objet de la demande est obligatoire.";
    if (empty($message)) $errors[] = "Le message est obligatoire.";

    // Validation du select
    $objets_valides = ['Portrait', 'Portrait Corporate', 'mariage', 'devis', 'autre'];
    if (!empty($objet_demande) && !in_array($objet_demande, $objets_valides)) {
        $errors[] = "L'objet de la demande n'est pas valide.";
    }

    // Vérification de l'email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // SI ERREURS → Stocker en session et rediriger vers le formulaire
    // if (!empty($errors)) {
    //     $_SESSION['form_errors'] = $errors;
    //     $_SESSION['form_data'] = $_POST;
    //     header('Location: ../../index.html#contactUs');
    //     exit();
    // }

    // SI ERREURS → Redirection vers confirmation avec les erreurs
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header('Location: confirmation.php');
        exit();
    }

    // INSERTION EN BASE
    try {
        $sqlVisitors = "INSERT INTO `visiteurs` 
            (`nom`, `prenom`, `email`, `objet_demande`, `date_evenement`, `message`) 
            VALUES 
            (:nom, :prenom, :email, :objet_demande, :date_evenement, :message)";
        $stmt = $pdo->prepare($sqlVisitors);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':objet_demande' => $objet_demande,
            ':date_evenement' => $date_evenement,
            ':message' => $message
        ]);

        // SUCCÈS → Redirection vers confirmation
        $_SESSION['success_message'] = "Votre message a été envoyé avec succès !";
        header('Location: confirmation.php');
        exit();
    } catch (Exception $e) {
        // ERREUR BDD → Redirection vers confirmation avec erreur
        $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement.";
        header('Location: confirmation.php');
        exit();
    }
} else {
    // Accès direct → Retour à l'accueil
    header('Location: ../../index.html');
    exit();
}
