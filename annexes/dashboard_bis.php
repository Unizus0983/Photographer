<?php
// Vérification de la connexion
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: connexion_admin.php');
    exit();
}

// Connexion à la base de données
require_once '../includes/connect.php';

// Récupération des données
// (Nous les simulerons pour l'instant)
$stats = [
    'files' => 24,
    'articles' => 15,
    'visitors' => 1245,
    'admins' => 3
];

$files = [
    ['name' => 'rapport.pdf', 'size' => '2.4 MB', 'type' => 'PDF', 'date' => '2023-11-10'],
    ['name' => 'image.png', 'size' => '1.2 MB', 'type' => 'Image', 'date' => '2023-11-08'],
    ['name' => 'document.docx', 'size' => '345 KB', 'type' => 'Document', 'date' => '2023-11-05']
];

$articles = [
    ['title' => 'Premier article', 'author' => 'Admin', 'date' => '2023-11-10', 'status' => 'Publié'],
    ['title' => 'Article en cours', 'author' => 'Admin', 'date' => '2023-11-09', 'status' => 'Brouillon']
];

$admins = [
    ['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => 'Superadmin', 'last_login' => '2023-11-10']
];
?>

<?php
$pageHeading = "Bienvenue sur Mon Site";
$pageTitle = $pageHeading . " — Mon Site";
$pageDescription = "Découvrez les dernières nouveautés et informations de Mon Site.";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

</html>
<?php include __DIR__ . '../includes/header.php'; ?>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="header">
            <h1><?php echo $pageHeading; ?></h1>
            //créer le fichier logout pour fin de session
            <form method="post" action="logout.php">
                <button type="submit" class="logout-btn">Déconnexion</button>
            </form>
        </header>

        <div class="main-content">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="admin-info">
                    <h3><?php echo $_SESSION['admin_name']; ?></h3>
                    <p><?php echo $_SESSION['admin_role']; ?></p>
                </div>

                <nav class="sidebar-nav">
                    <a href="#" class="nav-item active" data-section="dashboard">Tableau de bord</a>
                    <a href="#" class="nav-item" data-section="files">Fichiers</a>
                    <a href="#" class="nav-item" data-section="articles">Articles</a>
                    <a href="#" class="nav-item" data-section="admins">Administrateurs</a>
                </nav>
            </aside>

            <!-- Content Area -->
            <main class="content">
                <!-- Dashboard Section -->
                <section id="dashboard-section" class="content-section active">
                    <h2>Tableau de bord</h2>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?php echo $stats['files']; ?></h3>
                            <p>Fichiers</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $stats['articles']; ?></h3>
                            <p>Articles</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $stats['visitors']; ?></h3>
                            <p>Visiteurs</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $stats['admins']; ?></h3>
                            <p>Administrateurs</p>
                        </div>
                    </div>

                    <div class="recent-activity">
                        <h3>Activité récente</h3>
                        <ul>
                            <li>Connexion de <?php echo $_SESSION['admin_name']; ?> - <?php echo date('d/m/Y H:i'); ?></li>
                            <li>Modification d'article - 09/11/2023 14:30</li>
                            <li>Upload de fichier - 08/11/2023 10:15</li>
                        </ul>
                    </div>
                </section>

                <!-- Files Section -->
                <section id="files-section" class="content-section">
                    <h2>Gestion des fichiers</h2>

                    <div class="upload-area">
                        <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="file" name="file" id="file-input" required>
                            <button type="submit">Uploader</button>
                        </form>
                    </div>

                    <div class="files-list">
                        <h3>Fichiers disponibles</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Taille</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td><?php echo $file['name']; ?></td>
                                        <td><?php echo $file['size']; ?></td>
                                        <td><?php echo $file['type']; ?></td>
                                        <td><?php echo $file['date']; ?></td>
                                        <td>
                                            <button class="btn-download">Télécharger</button>
                                            <button class="btn-delete">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Articles Section -->
                <section id="articles-section" class="content-section">
                    <h2>Gestion des articles</h2>

                    <div class="article-form">
                        <h3>Créer un nouvel article</h3>
                        <form action="save_article.php" method="post">
                            <div class="form-group">
                                <label for="article-title">Titre</label>
                                <input type="text" id="article-title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="article-content">Contenu</label>
                                <textarea id="article-content" name="content" rows="6" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="article-status">Statut</label>
                                <select id="article-status" name="status">
                                    <option value="draft">Brouillon</option>
                                    <option value="published">Publié</option>
                                </select>
                            </div>
                            <button type="submit">Enregistrer</button>
                        </form>
                    </div>

                    <div class="articles-list">
                        <h3>Articles existants</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?php echo $article['title']; ?></td>
                                        <td><?php echo $article['author']; ?></td>
                                        <td><?php echo $article['date']; ?></td>
                                        <td><?php echo $article['status']; ?></td>
                                        <td>
                                            <button class="btn-edit">Modifier</button>
                                            <button class="btn-delete">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Admins Section -->
                <section id="admins-section" class="content-section">
                    <h2>Gestion des administrateurs</h2>

                    <div class="admin-form">
                        <h3>Ajouter un administrateur</h3>
                        <form action="add_admin.php" method="post">
                            <div class="form-group">
                                <label for="admin-name">Nom complet</label>
                                <input type="text" id="admin-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="admin-email">Email</label>
                                <input type="email" id="admin-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="admin-password">Mot de passe</label>
                                <input type="password" id="admin-password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="admin-role">Rôle</label>
                                <select id="admin-role" name="role">
                                    <option value="admin">Administrateur</option>
                                    <option value="superadmin">Super Administrateur</option>
                                </select>
                            </div>
                            <button type="submit">Ajouter</button>
                        </form>
                    </div>

                    <div class="admins-list">
                        <h3>Administrateurs existants</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Dernière connexion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo $admin['name']; ?></td>
                                        <td><?php echo $admin['email']; ?></td>
                                        <td><?php echo $admin['role']; ?></td>
                                        <td><?php echo $admin['last_login']; ?></td>
                                        <td>
                                            <button class="btn-edit">Modifier</button>
                                            <button class="btn-delete">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>

</html>