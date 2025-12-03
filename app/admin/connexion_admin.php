<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/connect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        try {
            $sql = "SELECT id_admin, username, password, role, is_active 
                    FROM admins 
                    WHERE username = :username 
                    AND is_active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Définir TOUTES les variables de session nécessaires
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_name'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin'] = true; // Pour la compatibilité

                // Mettre à jour le last_login
                $updateSql = "UPDATE admins SET last_login = NOW() WHERE id_admin = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([':id' => $admin['id_admin']]);

                session_regenerate_id(true);

                header('Location: dashboard.php');
                exit();
            } else {
                $errors[] = "Identifiant ou mot de passe incorrects";
            }
        } catch (PDOException $e) {
            error_log("Erreur de connexion admin: " . $e->getMessage());
            $errors[] = "Erreur de connexion";
        }
    } else {
        $errors[] = "Tous les champs sont requis";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<?php
$pageTitle = " Espace créatif  Unizus-art";
$pageDescription = "creative studio";
include '../includes/head.php';
?>

<body>
    <div class="container-login">
        <div class="login-box">
            <h1>🔏 Connexion Admin</h1>
            <form method="post" action="">
                <input type="text" name="username" placeholder="Nom d'utilisateur" autocomplete="username">
                <input type="password" name="password" placeholder="Mot de passe" autocomplete="off">
                <div class="btnActions">
                    <button type="submit" class="btn btn-submit">Se connecter</button>
                    <a class="btn btn-return" href="../../index.html">← accueil site</a>
                </div>

            </form>
        </div>


        <!-- Messages d'erreur unifiés -->
        <?php if (!empty($errors)): ?>
            <div class="alert-message error">
                <span class="message-icon">❌</span>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Message de déconnexion -->
        <?php if (isset($_GET['logout'])): ?>
            <div class="alert-message success">
                <span class="message-icon">✅</span>
                Déconnexion réussie.
            </div>
        <?php endif; ?>
    </div>
</body>

</html>