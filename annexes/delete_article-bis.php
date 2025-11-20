<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: connexion_admin.php');
    exit();
}

require_once '../includes/connect.php';

$message = '';
$success = false;

// Si confirmation reçue via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $id = (int)$_POST['id'];

    if ($id > 0) {
        try {
            // 1. Récupérer les infos de l'article avant suppression
            $sqlSelect = "SELECT titre, image FROM articles WHERE id = ?";
            $stmtSelect = $pdo->prepare($sqlSelect);
            $stmtSelect->execute([$id]);
            $article = $stmtSelect->fetch();

            if ($article) {
                // 2. Supprimer l'article de la base
                $sqlDelete = "DELETE FROM articles WHERE id = ?";
                $stmtDelete = $pdo->prepare($sqlDelete);
                $result = $stmtDelete->execute([$id]);

                if ($result && $stmtDelete->rowCount() > 0) {
                    // 3. Supprimer l'image du serveur si elle existe
                    if (!empty($article['image'])) {
                        $imagePath = __DIR__ . '/../uploads/' . $article['image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }

                    $success = true;
                    $_SESSION['success_message'] = "✅ L'article '" . htmlspecialchars($article['titre']) . "' a été supprimé avec succès";
                } else {
                    $_SESSION['error_message'] = "❌ Erreur lors de la suppression de l'article";
                }
            } else {
                $_SESSION['error_message'] = "❌ Article non trouvé";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "❌ Erreur de base de données: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "❌ ID d'article invalide";
    }

    // Rediriger vers la page des articles
    header('Location: articles.php');
    exit();
}

// Si ID reçu en GET pour afficher la page de confirmation
if (isset($_GET['id'])) {
    // $id = (int)$_GET['id'];

    if ($id > 0) {
        try {
            // Récupérer l'article pour afficher les infos
            $sql = "SELECT id, titre, contenu, image, date_publication FROM articles WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $article = $stmt->fetch();

            if (!$article) {
                $_SESSION['error_message'] = "❌ Article non trouvé";
                header('Location: articles.php');
                exit();
            }
        } catch (PDOException $e) {
            $message = "❌ Erreur: " . $e->getMessage();
        }
    } else {
        header('Location: articles.php');
        exit();
    }
} else {
    // Si pas d'ID, rediriger
    header('Location: articles.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Confirmer la suppression</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .confirmation-box {
            border: 2px solid #dc3545;
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .warning-icon {
            font-size: 48px;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }

        .article-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        h2 {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="confirmation-box">
        <div class="warning-icon">⚠️</div>
        <h2>Confirmation de suppression</h2>

        <div class="message">
            <p><strong>Êtes-vous sûr de vouloir supprimer cet article ?</strong></p>
            <p><em>Cette action est irréversible !</em></p>
        </div>

        <!-- Aperçu de l'article -->
        <div class="article-preview">
            <h3><?= htmlspecialchars($article['titre']) ?></h3>
            <p><strong>Extrait :</strong>
                <?= substr(strip_tags($article['contenu']), 0, 150) ?><?= strlen(strip_tags($article['contenu'])) > 150 ? '...' : '' ?>
            </p>
            <p><strong>Date de publication :</strong> <?= $article['date_publication'] ?></p>
            <?php if (!empty($article['image'])): ?>
                <p><strong>Image :</strong> Oui</p>
            <?php else: ?>
                <p><strong>Image :</strong> Aucune</p>
            <?php endif; ?>
        </div>

        <!-- Boutons d'action -->
        <div class="action-buttons">
            <!-- Formulaire de suppression -->
            <form method="POST" style="display: inline;">
                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn btn-danger">
                    🗑️ Oui, supprimer définitivement
                </button>
            </form>

            <!-- Bouton Annuler -->
            <a href="articles.php" class="btn btn-secondary">
                ↩️ Annuler et retourner aux articles
            </a>
        </div>
    </div>
</body>

</html>