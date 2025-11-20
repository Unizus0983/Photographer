<?php
require_once '../includes/connect.php';
require_once '../includes/auth.php';

// Vérification simple de l'authentification
checkAdminAuth();

// Récupération des stats
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalDocs = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$totalVistors = $pdo->query("SELECT COUNT(*) FROM visiteurs")->fetchColumn();

// Récupération du last_login depuis la base de données
$lastLoginStmt = $pdo->prepare("SELECT last_login FROM admins WHERE id_admin = :id");
$lastLoginStmt->execute([':id' => $_SESSION['admin_id']]);
$lastLogin = $lastLoginStmt->fetchColumn();

// Formater la date du dernier login
$lastLoginFormatted = $lastLogin ? date('d/m/Y à H:i', strtotime($lastLogin)) : 'Première connexion';
?>
<!DOCTYPE html>
<html lang="fr">
<?php
$pageTitle = "Gestion des projets photos";
$pageDescription = "Plateforme de gestion des projets et portfolios photographiques "; // Optionnel
include '../includes/head.php';
?>




<body>
    <header>
        <h1>Tableau de bord</h1>
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

    <section class="container_stat container">
        <h2>Informations</h2>
        <div class="stat">
            <ul>
                <li class="card">Articles<br><span class="number"><?= $totalArticles ?></span> </li>
                <li class="card">Documents<br><span class="number"><?= $totalDocs ?></span> </li>
                <li class="card">visiteurs<br><span class="number"><?= $totalVistors ?></span> </li>
            </ul>
        </div>
    </section>

    <section class="container_stat container">
        <h2>Actions rapides</h2>
        <div class="stat">
            <ul>
                <li><a class="btn action" href="articles.php">Gérer les articles</a></li>
                <li><a class="btn action" href="documents.php">Gérer les documents</a></li>
                <li><a class="btn action" href="gestion_visiteurs.php">Gérer les Visiteurs</a></li>

                <?php if ($_SESSION['admin_role'] === 'superadmin'): ?>
                    <li><a class="btn action admin" href="gestion_admin.php">gestion admins</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </section>
</body>

</html>