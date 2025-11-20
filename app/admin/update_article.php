<?php
session_start();
require_once '../includes/config.php'; // ← CHANGE: utiliser config.php au lieu de connect.php

// Vérification admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);

    // Validation
    if (empty($titre) || empty($contenu)) {
        $_SESSION['error_message'] = "Le titre et le contenu sont obligatoires"; // ← STANDARD
        header("Location: modify_article.php?id=" . $id); // ← CORRIGE: modify_article.php
        exit();
    }

    try {
        // Récupérer l'article actuel pour l'image
        $stmt = $pdo->prepare("SELECT image FROM articles WHERE id_article = :id");
        $stmt->execute([':id' => $id]);
        $articleActuel = $stmt->fetch();

        if (!$articleActuel) {
            $_SESSION['error_message'] = "❌ Article non trouvé"; // ← STANDARD
            header("Location: articles.php");
            exit();
        }

        $nomImage = $articleActuel['image'];

        // Gestion de la suppression d'image
        if (isset($_POST['supprimer_image']) && $_POST['supprimer_image'] == 1) {
            if (!empty($nomImage)) {
                $cheminImage = '../uploads/' . $nomImage;
                if (file_exists($cheminImage)) {
                    unlink($cheminImage);
                }
                $nomImage = null;
            }
        }

        // Gestion du nouvel upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
            // Supprimer l'ancienne image si elle existe
            if (!empty($articleActuel['image'])) {
                $ancienChemin = '../uploads/' . $articleActuel['image'];
                if (file_exists($ancienChemin)) {
                    unlink($ancienChemin);
                }
            }

            // Uploader la nouvelle image
            $file = $_FILES['image'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($extension);

            if (in_array($extension, $extensionsAutorisees)) {
                // Générer un nom unique
                $nomImage = uniqid() . '.' . $extension;
                $cheminDestination = '../uploads/' . $nomImage;

                if (!move_uploaded_file($file['tmp_name'], $cheminDestination)) {
                    throw new Exception("Erreur lors de l'upload de l'image");
                }
            } else {
                $_SESSION['error_message'] = "Format d'image non autorisé"; // ← STANDARD
                header("Location: modify_article.php?id=" . $id);
                exit();
            }
        }

        // Mettre à jour l'article
        $stmt = $pdo->prepare("UPDATE articles SET titre = :titre, contenu = :contenu, image = :image WHERE id_article = :id");
        $stmt->execute([
            ':titre' => $titre,
            ':contenu' => $contenu,
            ':image' => $nomImage,
            ':id' => $id
        ]);

        $_SESSION['success_message'] = "Article modifié avec succès !"; // ← STANDARD
        header("Location: articles.php");
        exit();
    } catch (Exception $e) {
        error_log("Erreur modification article: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la modification de l'article"; // ← STANDARD
        header("Location: modify_article.php?id=" . $id);
        exit();
    }
} else {
    $_SESSION['error_message'] = "Requête invalide"; // ← STANDARD
    header("Location: articles.php");
    exit();
}
