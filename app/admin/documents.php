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

// Pour  compatibilité avec  code existant
$message = $message_success ?: $message_error;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES["fichier"]) && $_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {

        $errors = [
            UPLOAD_ERR_INI_SIZE   => "Fichier trop volumineux (php.ini)",
            UPLOAD_ERR_FORM_SIZE  => "Fichier trop volumineux (formulaire)",
            UPLOAD_ERR_PARTIAL    => "Téléchargement interrompu",
            UPLOAD_ERR_NO_FILE    => "Aucun fichier sélectionné",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "Erreur d'écriture",
            UPLOAD_ERR_EXTENSION  => "Extension PHP a stoppé le téléchargement"
        ];

        $uploadsDir = __DIR__ . '/../uploads/';
        $allowed = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp'
        ];

        $filename  = basename($_FILES['fichier']["name"]);
        $filesize  = $_FILES['fichier']["size"];
        $fileTmp   = $_FILES['fichier']['tmp_name'];
        $tailleKo  = round($filesize / 1024, 2);

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Vérification extension
        if (!array_key_exists($extension, $allowed)) {
            $message = "Extension non autorisée";
        }

        // Vérification MIME réel
        if (empty($message)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $fileTmp);
            finfo_close($finfo);

            if (!in_array($realMime, array_values($allowed))) {
                $message = "Type MIME incorrect ($realMime)";
            }
        }

        // Vérification taille
        if (empty($message) && $filesize > 358400) {
            $message = "Fichier trop volumineux (max 350 Ko)";
        }

        // Si tout est OK
        if (empty($message)) {
            $newName = md5(uniqid('doc_', true)) . "." . $extension;
            $targetFile = $uploadsDir . $newName;

            if (file_exists($targetFile)) {
                $message = "Erreur : conflit de nom de fichier";
            } elseif (!move_uploaded_file($fileTmp, $targetFile)) {
                $message = "Erreur lors de l'enregistrement du fichier";
            } else {
                chmod($targetFile, 0644);

                try {
                    $sql = "INSERT INTO documents 
                        (id_admin, nom_original, nom_fichier, type_fichier, taille_ko) 
                        VALUES (:id_admin, :nom_original, :nom_fichier, :type_fichier, :taille_ko)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':id_admin'      => $_SESSION['admin_id'],
                        ':nom_original'  => $filename,
                        ':nom_fichier'   => $newName,
                        ':type_fichier'  => $extension,
                        ':taille_ko'     => $tailleKo
                    ]);

                    $_SESSION['success_message'] = "Fichier uploadé et enregistré en base avec succès";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } catch (PDOException $e) {
                    unlink($targetFile);
                    $message = "Erreur BDD : " . $e->getMessage();
                }
            }
        }
    } else {
        $message = "Aucun fichier envoyé";
    }
}



//récupérer les fichiers
try {
    $sql = "SELECT * FROM documents ORDER BY date_upload DESC";
    $stmt = $pdo->query($sql);
    $fichiers = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des fichiers: " . $e->getMessage();
    $fichiers = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = "Gestion des documents";
$pageDescription = "Gestion des fichiers et des photographies du site ";
include '../includes/head.php';
?>


<body class="dashboard-article">

    <header>
        <h1>Charger un document</h1>
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

    <!-------- Messages de session ------->
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

    <!-- Pour garder compatibilité avec  anciens messages  dés fois que -->
    <?php if (!empty($message) && empty($message_success) && empty($message_error)): ?>
        <div class="alert-message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <span class="message-icon"><?= strpos($message, '✅') !== false ? '✅' : '❌' ?></span>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <!-- fin partie messages de session -->

    <section class="container containerForm">
        <h2>Chargement de fichier</h2>
        <form method="post" enctype="multipart/form-data" id="form-fichier">
            <div class="commandes">
                <small>Fichiers autorisés : PDF, JPG, JPEG, WEBP, max 350ko</small>
                <div class="charger">
                    <label class="downloadFile" for="fileInput">
                        <span class="material-symbols-outlined icon">
                            <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="#886c10">
                                <path d="M480-320 280-520l56-58 104 104v-326h80v326l104-104 56 58-200 200ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z" />
                            </svg>
                        </span>
                        <span class="labelName">Importer un fichier</span>
                        <span class="fileName">Aucun fichier sélectionné</span>
                    </label>
                    <input type="file" name="fichier" id="fileInput" accept=".pdf,.jpg,.jpeg,.webp" required>
                    <button class="btn" type="button" id="cancelFile" style="display:none;">❌ Annuler</button>
                </div>
                <button type="submit" class="btn btn-submit">Charger le fichier</button>
            </div>
        </form>
        <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
    </section>

    <section class="container container-article-card">
        <h2>Liste des fichiers chargés</h2>

        <?php if (empty($fichiers)): ?>
            <p>Aucun fichier chargé pour le moment.</p>
        <?php else: ?>
            <div class="fichiers-liste">
                <?php foreach ($fichiers as $fichier):
                    // Déterminer l'icône selon le type de fichier
                    $icone = '';
                    $classe = '';
                    switch (strtolower($fichier['type_fichier'])) {
                        case 'pdf':
                            $icone = '📄';
                            $classe = 'pdf';
                            break;
                        case 'jpg':
                        case 'jpeg':
                        case 'webp':
                            $icone = '🖼️';
                            $classe = 'image';
                            break;
                        default:
                            $icone = '📎';
                            $classe = 'autre';
                    }
                ?>
                    <div class="fichier-item">
                        <div class="fichier-info">
                            <div class="fichier-nom">
                                <span class="icone-fichier <?= $classe ?>"><?= $icone ?></span>
                                <?= htmlspecialchars($fichier['nom_original']) ?>
                                <?php if (in_array(strtolower($fichier['type_fichier']), ['jpg', 'jpeg', 'webp'])): ?>
                                    <!-- Bouton VOIR avec modal -->
                                    <button type="button"
                                        class="btn-view"
                                        onclick="afficherImage('../uploads/<?= htmlspecialchars($fichier['nom_fichier']) ?>')">
                                        👁️
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="fichier-details">
                                <span>📅 Chargé le <?= date('d/m/Y à H:i', strtotime($fichier['date_upload'])) ?></span>
                                <span>📊 <?= htmlspecialchars($fichier['taille_ko']) ?> Ko</span>
                                <span>📝 <?= strtoupper(htmlspecialchars($fichier['type_fichier'])) ?></span>
                            </div>

                        </div>
                        <div class="fichier-actions">

                            <!-- Téléchargement uniquement -->
                            <a href="../uploads/<?= htmlspecialchars($fichier['nom_fichier']) ?>"
                                download="<?= htmlspecialchars($fichier['nom_original']) ?>"
                                class="btn btn-success">
                                ⬇️ Télécharger
                            </a>

                            <!-- Supprimer -->
                            <form method="POST" action="delete_fichier.php" style="display:inline;"
                                onsubmit="return confirm('Supprimer le fichier &quot;<?= htmlspecialchars($fichier['nom_original'], ENT_QUOTES) ?>&quot; ?')">
                                <input type="hidden" name="id" value="<?= $fichier['id_document'] ?>">
                                <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p><em>Total : <?= count($fichiers) ?> fichier(s)</em></p>
        <?php endif; ?>

        <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
    </section>
    <!-- Modal pour afficher les images -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script src="../../assets/js/uploads.js"></script>
    <script src="../../assets/js/modal_doc.js"></script>

</body>

</html>