<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';
checkAdminAuth();

// Récupérer l'article à modifier
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT id_article, titre, contenu, image FROM articles WHERE id_article = :id");
    $stmt->execute([':id' => $id]);
    $article = $stmt->fetch();

    if (!$article) {
        $_SESSION['error_message'] = "Article non trouvé";
        header("Location: articles.php");
        exit();
    }
} else {
    header("Location: articles.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageHeading = "Gestion et modification des articles";
$pageDescription = "Gestion et modification des articles";
include '../includes/head.php';
?>

<body class="dashboard-article">

    <header>
        <h1>Modifications articles</h1>
        <div class="identity">
            <p>Bienvenue <span class="nameAdmin"><?= htmlspecialchars($_SESSION['admin_name']) ?> </span> !</p>
            <p>Votre dernier login :
                <span class="datelogin"><?= $lastLoginFormatted ?></span>
            </p>
            <p class="role-badge">Rôle : <?= htmlspecialchars($_SESSION['admin_role']) ?></p>
            <?php if ($_SESSION['admin_role'] === 'superadmin'): ?>
                <span class="fondateur-badge">
                    ⭐ Super Admin
                    <?php if ($_SESSION['admin_id'] == 1): ?>
                        (Fondateur)
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
        <a class="btn logout" href="admin_logout.php">Se déconnecter</a>
        <a class="btn logout" href="../../index.html" target="_blank">Voir le site</a>
    </header>

    <!-- Messages de session unifiés -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-message success">
            <span class="message-icon">✅</span>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert-message error">
            <span class="message-icon">❌</span>
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="container containerForm">
        <h2>Modifier l'article</h2>
        <form method="POST" action="update_article.php" enctype="multipart/form-data" id="form-article">
            <input type="hidden" name="id" value="<?= $article['id_article'] ?>">

            <div>
                <label for="titre">Titre :</label>
                <input type="text"
                    id="titre"
                    name="titre"
                    value="<?= htmlspecialchars($article['titre']) ?>"
                    oninput="this.value = this.value.toLowerCase().replace(/\b\w/g, char => char.toUpperCase())" required>
            </div>

            <div>
                <label for="contenu">Contenu :</label>
                <textarea id="contenu" name="contenu" required><?= $article['contenu'] ?></textarea>
            </div>

            <div>
                <label>Image actuelle :</label>
                <?php if (!empty($article['image'])): ?>
                    <div class="current-image">
                        <p>Réfèrence : <?= htmlspecialchars($article['image']) ?></p>
                        <img src="../uploads/<?= htmlspecialchars($article['image']) ?>" alt="Image actuelle" class="image-preview">
                        <br>
                        <label>
                            <input type="checkbox" name="supprimer_image" value="1" id="delete-checkbox">
                            <span>🗑️ Supprimer cette image</span>
                        </label>
                    </div>
                <?php else: ?>
                    <p>Aucune image actuellement</p>
                <?php endif; ?>
            </div>

            <div class="commandes">
                <small>Images autorisées : jpeg, jpg, webp, max 350ko</small>
                <small>Image Non Obligatoire</small>
                <div class="charger">
                    <label class="downloadFile" for="fileInput">
                        <span class="material-symbols-outlined icon">
                            <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#886c10">
                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z" />
                            </svg>
                        </span>
                        <span class="labelName">Importer une photo</span>
                        <span class="fileName"></span>
                    </label>
                    <input type="file" name="image" id="fileInput" accept=".jpg,.jpeg,.webp">
                    <button class="btn" type="button" id="cancelFile" style="display:none;">❌ Annuler</button>
                </div>

                <button type="submit" class="btn btn-submit">✒ Modifier l'article</button>
                <a href="articles.php" class="btn btn-submit">❌ Annuler</a>
            </div>
        </form>
        <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
    </section>
    <script src="https://cdn.tiny.cloud/1/f9q30fk0botcmau80oveeqyrwfg7fecgju3dalvzo27nm9k3/tinymce/6/tinymce.min.js" defer></script>

    <script src="../../assets/js/uploads.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#contenu', // Dans articles.php
                // OU selector: '#contenu', // Dans modify_article.php
                plugins: 'advlist lists link wordcount',
                toolbar: 'bold italic underline | h2 h3 | bullist numlist | link',

                menubar: false,
                height: 300,
                branding: false,
                iframe_attrs: {
                    'title': 'Éditeur de texte riche - Rédaction du contenu de l\'article'
                },
                formats: {
                    underline: {
                        inline: 'u',
                        exact: true
                    }
                },
                // 🔒 Sécurité
                valid_elements: 'h2,h3,p,br,strong,em,u,ul,ol,li,a[href|target|rel]',
                valid_children: '+li[p]',
                cleanup: true,
                verify_html: true,
                // 🚀 Performance
                paste_data_images: false,
                images_upload_url: 'false',
                // ✅ UNIQUEMENT CES 2 LIGNES POUR LES LIENS
                default_link_target: '_blank',
            });
        });
    </script>


</body>

</html>