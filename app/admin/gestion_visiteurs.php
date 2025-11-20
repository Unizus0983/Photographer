<?php

require_once '../includes/config.php';

$allVisitors = [];
$message = '';
$totalPages = 1;
$currentPage = 1;
$totalResultats = 0;

// gestion de la suppression d'un visiteurs
if (isset($_POST['supprimer_visiteur'])) {
    $idVisiteur = (int)$_POST['id_visiteur'];

    try {
        $sqlDelete = "DELETE FROM visiteurs WHERE id_visiteur = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([':id' => $idVisiteur]);
        //si nombre de lignes
        if ($stmtDelete->rowCount() > 0) {
            $_SESSION['success'] = "Visiteur supprimé avec succès !";
        } else {
            $_SESSION['error'] = "Aucun visiteur trouvé avec cet ID.";
        }

        // Redirection sur la même page réactualiser pour éviter la re-soumission du formulaire avec concaténation de la page actuelle + paramètres de l' url
        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
        exit();
    }
}

// AFFICHAGE DES MESSAGES
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// pagination pour nombre de visiteurs par page
$visitorsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// GESTION DES RECHERCHES 
// barre de recherche
// variable de la saise en barre de recherche input type search et variable du select
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
//voir Filtre pour la recherche par le select
$filterObjet = isset($_GET['objet']) ? $_GET['objet'] : '';

try {
    // where = astuce pour toujours ajouter AND facilement/ where 1=1 toujours vrai [POUR BASE bdd VISITEURS] car règle mathématique universel pour tous types de base de données
    $sql = "SELECT *
            FROM `visiteurs` 
            WHERE 1=1";

    $params = [];

    //requête en lego par concaténation $sql .=  L'OPÉRATEUR DE CONCATÉNATION pour passer en revue chaque point à défaut de savoir ce que l'utilisateur va prendre comme critères de recherche 
    if (!empty($search)) {
        $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR message LIKE ?)";
        //recherche multis critères  SUR LA BASE DE LA VARIABLE récupérée par l'input de type search ligne 58 par method get
        $searchTerm = "%$search%";
        // tableau de tous les critères nom prenom email message
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    //Filtre pour la recherche

    if (!empty($filterObjet) && $filterObjet !== 'tous') {
        $sql .= " AND objet_demande = ?";
        $params[] = $filterObjet;
    }

    $sql .= " ORDER BY `date_soumission` DESC";

    // Requête pour le total
    $sqlCount = "SELECT COUNT(*) FROM visiteurs WHERE 1=1";
    $paramsCount = [];

    if (!empty($search)) {
        $sqlCount .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR message LIKE ?)";
        $paramsCount = array_merge($paramsCount, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    if (!empty($filterObjet) && $filterObjet !== 'tous') {
        $sqlCount .= " AND objet_demande = ?";
        $paramsCount[] = $filterObjet;
    }

    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($paramsCount);
    $totalResultats = $stmtCount->fetchColumn();
    $totalPages = ceil($totalResultats / $visitorsPerPage);

    // Ajout LIMIT voir variables currentPage et visitorsPerPage à pagination pour visiteurs par page 
    $offset = ($currentPage - 1) * $visitorsPerPage;
    $sql .= " LIMIT $offset, $visitorsPerPage";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $allVisitors = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT DISTINCT objet_demande FROM visiteurs ORDER BY objet_demande");
    $objets = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $message = "Erreur du chargement: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="fr">

<?php
$pageHeading = "Gestion des visiteurs";
$pageDescription = "Gestion des visiteurs et de leurs messages";
include '../includes/head.php';
?>

<body>
    <header>
        <h1>Gestion des visiteurs</h1>
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
    <section class="container container-search ">
        <h2>📊 Gestion visiteurs</h2>



        <?php if ($message): ?>
            <div class="alert-message <?= strpos($message, '❌') !== false ? 'error' : 'success' ?>">
                <span class="message-icon"><?= strpos($message, '❌') !== false ? '❌' : '✅' ?></span>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats">
            <strong>📈 <?= number_format($totalResultats ?? 0, 0, ',', ' ') ?></strong> visiteurs au total
            <?php if (!empty($search)): ?>
                - Recherche : "<?= htmlspecialchars($search) ?>"
            <?php endif; ?>
        </div>

        <!-- filtre de recherche -->
        <form method="GET" class=" filters-container">
            <div class="filter-group">
                <label for="search">🔍 Rechercher</label>
                <input type="search"
                    id="search"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher un visiteur..."
                    aria-label="Recherche parmi les visiteurs">
            </div>

            <div class="filter-group">
                <label for="objet">🎯 Filtre par objet</label>
                <select id="objet" name="objet">
                    <option value="tous">Tous les objets</option>
                    <?php foreach ($objets as $objet): ?>
                        <option value="<?= htmlspecialchars($objet) ?>"
                            <?= $filterObjet === $objet ? 'selected' : '' ?>>
                            <?= htmlspecialchars($objet) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group actions">
                <button type="submit" class="btn btn-submit">Filtrer</button>
                <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-danger">Réinitialiser</a>
                <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
            </div>
        </form>
    </section>

    <!-- Résultats -->
    <?php if (empty($allVisitors)): ?>
        <div class="avertissement" style="text-align: center; padding: 40px; color: #6c757d;">
            <?= empty($search) ? 'Aucun visiteur enregistré.' : 'Aucun résultat pour votre recherche.' ?>
        </div>
    <?php else: ?>
        <section class="container container-visiteurs">
            <div class="visitors-grid">
                <?php foreach ($allVisitors as $visitor): ?>
                    <div class="visitor-card">
                        <div class="visitor-content">
                            <div class="visitor-header">
                                <div class="visitor-name">
                                    <?= htmlspecialchars($visitor['prenom']) ?> <?= htmlspecialchars($visitor['nom']) ?>
                                </div>
                                <div class="visitor-id">#<?= htmlspecialchars($visitor['id_visiteur']) ?></div>
                            </div>

                            <div class="visitor-detail">
                                <span class="detail-label">📧 Email</span>
                                <span class="detail-value"><?= htmlspecialchars($visitor['email']) ?></span>
                            </div>

                            <div class="visitor-detail">
                                <span class="detail-label">🎯 Objet</span>
                                <span class="detail-value"><?= htmlspecialchars($visitor['objet_demande']) ?></span>
                            </div>

                            <div class="visitor-detail">
                                <span class="detail-label">📅 Date événement</span>
                                <span class="detail-value"><?= htmlspecialchars($visitor['date_evenement']) ?></span>
                            </div>

                            <div class="visitor-detail">
                                <span class="detail-label">💬 Message</span>
                                <div class="message-content">
                                    <?= nl2br(htmlspecialchars($visitor['message'])) ?>
                                </div>
                            </div>

                            <div class="visitor-detail">
                                <span class="detail-label">⏰ Soumis le</span>
                                <span class="detail-value"><?= htmlspecialchars($visitor['date_soumission']) ?></span>
                            </div>
                        </div>
                        <!-- BOUTON SUPPRESSION  -->
                        <div class="visitor-actions">
                            <form method="POST">
                                <input type="hidden" name="id_visiteur" value="<?= $visitor['id_visiteur'] ?>">
                                <button type="submit" name="supprimer_visiteur" class="btn btn-danger "
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce visiteur ?')">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                        <!-- FIN BOUTON SUPPRESSION -->
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>


            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&objet=<?= urlencode($filterObjet) ?>" class="btn btn-return">← Précédent</a>
                    <?php endif; ?>

                    <span class="page-info">
                        Page <?= $currentPage ?> sur <?= $totalPages ?>
                    </span>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&objet=<?= urlencode($filterObjet) ?>" class="btn btn-return">→ Suivant →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </section>
    <?php endif; ?>

</body>

</html>