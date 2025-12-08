<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';
checkAdminAuth();

// if (!isset($_SESSION['admin_id'])) {
//     header('Location: login.php');
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    if ($id > 0) {
        try {
            $checkStmt = $pdo->prepare("SELECT id_article, titre, image FROM articles WHERE id_article = :id");
            $checkStmt->execute([':id' => $id]);
            $article = $checkStmt->fetch();

            if ($article) {
                $fileDeleted = false;

                // Supprimer le fichier image
                if (!empty($article['image'])) {
                    $imagePath = '../uploads/' . $article['image'];
                    // $thumbnailPath = '../uploads/thumbs/' . $article['image']; //si dossier miniatures dans dossier uploads
                    // Supprimer l'image principale
                    if (file_exists($imagePath) && is_file($imagePath)) {
                        if (unlink($imagePath)) {
                            $fileDeleted = true;
                        }
                    }

                    // Supprimer la miniature si elle existe
                    // if (file_exists($thumbnailPath) && is_file($thumbnailPath)) {
                    //     unlink($thumbnailPath);
                    // }
                }

                // Supprimer de la base de données
                $stmt = $pdo->prepare("DELETE FROM articles WHERE id_article = :id");
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount() > 0) {
                    $message = "L'article '" . htmlspecialchars($article['titre']) . "' a été supprimé avec succès";
                    if ($fileDeleted) {
                        $message .= " (fichier image inclus)";
                    } else if (!empty($article['image'])) {
                        $message .= " (l'image n'a pas pu être supprimée)";
                    }
                    $_SESSION['success_message'] = $message . ".";
                } else {
                    $_SESSION['error_message']  = "Erreur lors de la suppression de l'article en base de données.";
                }
            } else {
                $$_SESSION['error_message']  = "Article non trouvé.";
            }
        } catch (PDOException $e) {
            error_log("Erreur suppression article: " . $e->getMessage());
            $_SESSION['error_message']  = "Une erreur technique est survenue.";
        }
    } else {
        $_SESSION['error_message']  = "ID d'article invalide.";
    }
} else {
    $_SESSION['error_message']  = "Requête invalide.";
}

header("Location: articles.php");
exit();
