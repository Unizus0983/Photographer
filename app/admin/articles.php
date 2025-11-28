<?php
require_once '../includes/config.php';

// Système unifié de messages
$message_success = "";
$message_error = "";

// Récupération des messages de session
if (isset($_SESSION['success_message'])) {
    $message_success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message_error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$message = ""; // Pour les erreurs immédiates

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_article') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    // VALIDATION IMMÉDIATE
    if (empty($titre) || empty($contenu)) {
        $message = "Le formulaire est incomplet - titre et contenu sont obligatoires";
    } else {
        $titre = strtoupper(strip_tags($titre));
        $imageName = null;

        // Vérification upload image
        if (!empty($_FILES['image']['name'])) {
            $uploadErrorCodes = [
                UPLOAD_ERR_INI_SIZE   => "Fichier trop volumineux (php.ini)",
                UPLOAD_ERR_FORM_SIZE  => "Fichier trop volumineux (formulaire)",
                UPLOAD_ERR_PARTIAL    => "Téléchargement interrompu",
                UPLOAD_ERR_NO_FILE    => "Aucun fichier sélectionné",
                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
                UPLOAD_ERR_CANT_WRITE => "Erreur d'écriture sur le disque",
                UPLOAD_ERR_EXTENSION  => "Extension PHP interdite"
            ];

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $message = ($uploadErrorCodes[$_FILES['image']['error']] ?? "Erreur inconnue");
            } else {
                $uploadsDir = __DIR__ . '/../uploads/';
                $allowed = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp'
                ];

                $filename  = $_FILES['image']["name"];
                $filesize  = $_FILES['image']["size"];
                $fileTmp   = $_FILES['image']['tmp_name'];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // Vérification extension
                if (!array_key_exists($extension, $allowed)) {
                    $message = "Extension non autorisée ($extension)";
                } else {
                    // Vérification MIME
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $realMime = finfo_file($finfo, $fileTmp);
                    // finfo_close($finfo);auto en PHP 

                    if (!in_array($realMime, array_values($allowed))) {
                        $message = "Type MIME incorrect ($realMime)";
                    } elseif ($filesize > 350 * 1024) {
                        $message = "Fichier photo trop volumineux (max 350 Ko)";
                    } else {
                        // Tout est OK
                        $newName   = md5(uniqid('img_', true)) . "." . $extension;
                        $targetFile = $uploadsDir . $newName;

                        if (move_uploaded_file($fileTmp, $targetFile)) {
                            chmod($targetFile, 0644);
                            $imageName = $newName;
                        } else {
                            $message = "Erreur lors de l'enregistrement du fichier";
                        }
                    }
                }
            }
        }

        // Insertion si pas d'erreur
        if (empty($message)) {
            $id_admin = $_SESSION['admin_id'];

            try {
                $sql = "INSERT INTO `articles`(`id_admin`, `titre`, `contenu`, `image`)
                        VALUES (:id_admin, :titre, :contenu, :image)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_admin' => $id_admin,
                    ':titre'    => $titre,
                    ':contenu'  => $contenu,
                    ':image'    => $imageName
                ]);

                // SUCCÈS : redirection immédiate
                $_SESSION['success_message'] = "Article '" . htmlspecialchars($titre) . "' ajouté avec succès !";
                header('Location: articles.php');
                exit();
            } catch (PDOException $e) {
                $message = "Erreur lors de l'ajout de l'article: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageHeading = "Gestion des articles";
$pageDescription = "Gérer l'ensemble des articles du site ";
include '../includes/head.php';
?>

<body class="dashboard-article">
    <header>
        <h1>gestion des articles</h1>
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
    <?php if (!empty($message_success)): ?>
        <div class="alert-message success">
            <span class="message-icon">✅</span>
            <?= htmlspecialchars($message_success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($message_error)): ?>
        <div class="alert-message error">
            <span class="message-icon">❌</span>
            <?= htmlspecialchars($message_error) ?>
        </div>
    <?php endif; ?>

    <!-- Messages d'erreur immédiats (sans redirection) -->
    <?php if (!empty($message)): ?>
        <div class="alert-message error">
            <span class="message-icon">❌</span>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <section class="container containerForm">
        <h2>Ajouter un article</h2>
        <form method="post" enctype="multipart/form-data" id="form-article">
            <div>
                <label for="title">Titre</label>
                <input type="text"
                    name="titre"
                    id="title"
                    value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                    oninput="this.value = this.value.toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
            </div>
            <div>
                <label for="contentArticle">Contenu</label>
                <textarea name="contenu" id="contentArticle"><?= $_POST['contenu'] ?? '' ?></textarea>
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
                <button name="action" value="add_article" class="btn btn-submit" type="submit">Soumettre</button>
            </div>
        </form>
        <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
    </section>

    <section class="container container-article-card">
        <h2>Articles existants</h2>
        <?php
        $sql = "SELECT * FROM articles ORDER BY date_publication DESC";
        $stmt = $pdo->query($sql);
        $articles = $stmt->fetchAll();
        ?>

        <?php if (empty($articles)): ?>
            <p>Aucun article pour le moment</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <?php
                $id = $article['id_article'] ?? 0;
                $titre = $article['titre'] ?? 'Titre manquant';
                $contenu = $article['contenu'] ?? '';
                $image = $article['image'] ?? '';
                $date = $article['date_publication'] ?? 'Date inconnue';
                ?>
                <article class="article-card">
                    <?php if (!empty($image)): ?>
                        <img src="../uploads/<?= htmlspecialchars($image) ?>" width="200" alt="Image article">
                    <?php else: ?>
                        <span>Aucune image</span>
                    <?php endif; ?>
                    <h1 class="title-article"><?= htmlspecialchars($titre) ?></h1>
                    <div class="content-contenu" style="white-space: pre-line;"><?= htmlspecialchars($contenu) ?></div>
                    <p><small>Créé le: <?= htmlspecialchars($date) ?></small></p>
                    <div class="article-actions">
                        <form action="delete_article.php" method="post" onsubmit="return confirm('Supprimer cet article ?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $article['id_article'] ?>">
                            <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
                        </form>
                        <a href="modify_article.php?id=<?= $article['id_article'] ?>" class="btn btn-success">
                            ✒ Modifier
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
    </section>
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/f9q30fk0botcmau80oveeqyrwfg7fecgju3dalvzo27nm9k3/tinymce/6/tinymce.min.js" defer></script>
    <script src="../../assets/js/uploads.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#contentArticle', // Dans articles.php
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