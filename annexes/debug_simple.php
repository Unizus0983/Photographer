<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🛠️ DEBUG SIMPLE</h1>";

// 1. Quelle session avez-vous ?
echo "<h2>1. VOTRE SESSION :</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Quel est le problème exact ?
echo "<h2>2. ACCÈS GESTION ADMIN :</h2>";
if ($_SESSION['admin_role'] === 'superadmin') {
    echo "<p style='color:green; font-size:20px;'>✅ VOUS DEVRIEZ AVOIR ACCÈS !</p>";
} else {
    echo "<p style='color:red; font-size:20px;'>❌ VOUS N'AVEZ PAS ACCÈS (Rôle: " . $_SESSION['admin_role'] . ")</p>";
}

// 3. Liens de test
echo "<h2>3. TESTEZ :</h2>";
echo "<p><a href='dashboard.php'>→ Tableau de bord</a></p>";
echo "<p><a href='gestion_admin.php'>→ Gestion admin (le problème)</a></p>";
echo "<p><a href='admin_logout.php'>→ Déconnexion</a></p>";
