<?php
session_start();
require_once '../includes/config.php';

// Vérification admin avec le nouveau système
if (!isset($_SESSION['admin_id'])) {
    header('Location: connexion_admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    try {
        // Récupérer les infos du fichier
        $stmt = $pdo->prepare("SELECT nom_fichier FROM documents WHERE id_document = :id");
        $stmt->execute([':id' => $id]);
        $fichier = $stmt->fetch();

        if ($fichier) {
            // Supprimer le fichier physique
            $cheminFichier = '../uploads/' . $fichier['nom_fichier'];
            if (file_exists($cheminFichier)) {
                unlink($cheminFichier);
            }

            // Supprimer de la base de données
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id_document = :id");
            $stmt->execute([':id' => $id]);

            $_SESSION['success_message'] = "Fichier supprimé avec succès"; // ← STANDARD
        } else {
            $_SESSION['error_message'] = "Fichier non trouvé"; // ← STANDARD
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage(); // ← STANDARD
    }
}

header('Location: documents.php');
exit();
