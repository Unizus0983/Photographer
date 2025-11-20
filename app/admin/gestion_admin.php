<?php
require_once '../includes/config.php';
// require_once '../includes/auth.php';

// Vérification SUPER ADMIN seulement
requireSuperAdmin();


$message_success = "";
$message_error = "";
// récup messages session
if (isset($_SESSION['success_message'])) {
    $message_success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message_error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


try {
    $stmt = $pdo->prepare("SELECT last_login FROM admins WHERE id_admin = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $adminData = $stmt->fetch();
    $lastLoginFormatted = $adminData && $adminData['last_login']
        ? date('d/m/Y à H:i', strtotime($adminData['last_login']))
        : 'Première connexion';
} catch (PDOException $e) {
    $lastLoginFormatted = 'Erreur de chargement';
}



// AJOUTER UN ADMINISTRATEUR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // Validation
    $errors = [];

    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est obligatoire";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide";
    }
    if (empty($role)) {
        $errors[] = "Le rôle est obligatoire";
    } elseif (!in_array($role, ['admin', 'superadmin'])) {
        $errors[] = "Rôle non valide";
    }

    // Si pas d'erreurs de validation
    if (empty($errors)) {
        // Vérifier si l'username existe déjà
        $checkStmt = $pdo->prepare("SELECT id_admin FROM admins WHERE username = :username");
        $checkStmt->execute([':username' => $username]);

        if ($checkStmt->fetch()) {
            $message_error = "Ce nom d'utilisateur existe déjà";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO admins (username, password, email, role, created_by) 
                        VALUES (:username, :password, :email, :role, :created_by)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':email' => $email,
                    ':role' => $role,
                    ':created_by' => $_SESSION['admin_id']
                ]);

                $message_success = "Administrateur ajouté avec succès";

                // Redirection pour éviter le rechargement du formulaire
                $_SESSION['success_message'] = $message_success;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (PDOException $e) {
                $message_error = "Erreur lors de l'ajout: " . $e->getMessage();
            }
        }
    } else {
        $message_error = implode("<br>", $errors);
    }
}

// RÉINITIALISER UN MOT DE PASSE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $admin_id = (int)$_POST['admin_id'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if ($admin_id === 1) {
        $message_error = "Impossible de modifier le mot de passe du Super Admin fondateur";
    } elseif (empty($new_password)) {
        $message_error = "Le nouveau mot de passe est obligatoire";
    } elseif (strlen($new_password) < 6) {
        $message_error = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif ($new_password !== $confirm_new_password) {
        $message_error = "Les mots de passe ne correspondent pas";
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            $sql = "UPDATE admins SET password = :password WHERE id_admin = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $admin_id
            ]);

            $message_success = "Mot de passe réinitialisé avec succès";
        } catch (PDOException $e) {
            $message_error = "Erreur lors de la réinitialisation: " . $e->getMessage();
        }
    }
    // ... après le code pour reset_password ...



    // ...  le code pour récupérer tous les admins ...
}
// ACTIVER/DÉSACTIVER UN ADMINISTRATEUR
if (isset($_GET['toggle'])) {
    $admin_id = (int)$_GET['toggle'];

    // Empêcher de se désactiver soi-même ou le super admin fondateur
    if ($admin_id === $_SESSION['admin_id']) {
        $message_error = "Vous ne pouvez pas vous désactiver vous-même";
    } elseif ($admin_id === 1) {
        $message_error = "Impossible de désactiver le Super Admin fondateur";
    } else {
        try {
            // Récupérer le statut actuel
            $checkStmt = $pdo->prepare("SELECT is_active FROM admins WHERE id_admin = :id");
            $checkStmt->execute([':id' => $admin_id]);
            $currentStatus = $checkStmt->fetchColumn();

            // Inverser le statut
            $newStatus = $currentStatus ? 0 : 1;

            $sql = "UPDATE admins SET is_active = :status WHERE id_admin = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':status' => $newStatus,
                ':id' => $admin_id
            ]);

            $message_success = $newStatus
                ? "Administrateur activé avec succès"
                : "Administrateur désactivé avec succès";

            // Redirection avec message en session
            $_SESSION['success_message'] = $message_success;
            header('Location: gestion_admin.php');
            exit();
        } catch (PDOException $e) {
            $message_error = "Erreur lors de la modification: " . $e->getMessage();
        }
    }
}


// Récupérer tous les admins
try {
    $sql = "SELECT a.*, creator.username as created_by_name 
            FROM admins a 
            LEFT JOIN admins creator ON a.created_by = creator.id_admin 
            ORDER BY a.created_at DESC";
    $stmt = $pdo->query($sql);
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $message_error = "Erreur: " . $e->getMessage();
    $admins = [];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php
    $pageHeading = "Gestion des administrateurs";
    $pageDescription = "Gérer les différents administrateurs du site";
    include '../includes/head.php';
    ?>

<body>
    <header>
        <h1>Gestion des admins</h1>
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

    <div class="admin-management container">
        <!-- Messages -->
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

        <!-- Formulaire d'ajout d'admin -->
        <div class="form-container">
            <h2>Ajouter un nouvel administrateur</h2>
            <form class="formAdmin" method="post">
                <input type="hidden" name="add_admin" value="1">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Nom d'utilisateur" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email *</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password"
                            placeholder="Minimum 6 caractères" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="confirmer mot de passe" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role">
                            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                            <option value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Administrateur</option>
                        </select>
                    </div>
                </div>

                <div class="btnActions">
                    <button type="submit" class="btn btn-submit">&#43; Ajouter admin</button>

                    <a class="btn btn-return" href="dashboard.php">← tableau de bord</a>
                </div>
            </form>

        </div>

        <!-- Liste des administrateurs -->
        <h2 class="adminList-title">👥 Liste des administrateurs</h2>
        <div class="admin-list">
            <?php foreach ($admins as $admin): ?>
                <div class="admin-card">
                    <div class="admin-info">
                        <h3>
                            <?= htmlspecialchars($admin['username']) ?>
                            <span class="role-<?= $admin['role'] ?>">
                                <?= $admin['role'] === 'superadmin' ? 'Super Admin' : 'Admin' ?>
                                <?php if ($admin['id_admin'] == 1): ?>
                                    ⭐
                                <?php endif; ?>
                            </span>
                        </h3>
                        <div class="admin-details">
                            <strong>Email:</strong> <?= htmlspecialchars($admin['email'] ?? 'Non renseigné') ?> |
                            <strong>Statut:</strong>
                            <span class="status-<?= $admin['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $admin['is_active'] ? '🟢 Actif' : '🔴 Inactif' ?>
                            </span> |
                            <strong>Créé par:</strong> <?= htmlspecialchars($admin['created_by_name'] ?? 'Système') ?> |
                            <strong>Dernière connexion:</strong>
                            <?= $admin['last_login'] ? date('d/m/Y à H:i', strtotime($admin['last_login'])) : '⏳ Jamais connecté' ?>
                        </div>

                        <!-- Formulaire de réinitialisation de mot de passe -->
                        <?php if ($admin['id_admin'] != 1): ?>
                            <div class="password-form">
                                <form method="post">
                                    <input type="hidden" name="admin_id" value="<?= $admin['id_admin'] ?>">
                                    <input type="hidden" name="reset_password" value="1">

                                    <div class="form-group">
                                        <label for="new"><small>Nouveau mot de passe:</small></label>
                                        <input type="password" id="new" name="new_password" placeholder="Min. 6 caractères" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label for="new-confirm"><small>Confirmation:</small></label>
                                        <input type="password" id="new-confirm" name="confirm_new_password" placeholder="Confirmer" autocomplete="off">
                                    </div>

                                    <button type=" submit" class="btn-warning">
                                        🔄 Réinitialiser
                                    </button>

                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-actions">
                        <?php if ($admin['id_admin'] != $_SESSION['admin_id'] && $admin['id_admin'] != 1): ?>
                            <a href="?toggle=<?= $admin['id_admin'] ?>"
                                class="btn <?= $admin['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                                <?= $admin['is_active'] ? '🚫 Désactiver' : '✅ Activer' ?>
                            </a>
                        <?php elseif ($admin['id_admin'] == 1): ?>
                            <span class="creator">
                                👑 Fondateur
                            </span>
                        <?php else: ?>
                            <span class="otherAdmin">
                                👤 Admin
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>