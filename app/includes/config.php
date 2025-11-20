<?php
session_start();
require_once '../includes/connect.php';

// Vérification de la connexion admin (sécurité)
if (!isset($_SESSION['admin_id'])) {
    header('Location: connexion_admin.php');
    exit();
}

// Récupération des données admin pour le header
$adminData = $pdo->prepare("SELECT username, last_login, role FROM admins WHERE id_admin = :id");
$adminData->execute([':id' => $_SESSION['admin_id']]);
$adminData = $adminData->fetch(PDO::FETCH_ASSOC);

if (!$adminData) {
    session_destroy();
    header('Location: connexion_admin.php');
    exit();
}

// Formatage des données
$lastLoginFormatted = $adminData['last_login'] ?
    date('d/m/Y à H:i', strtotime($adminData['last_login'])) :
    'Première connexion';

// Variables disponibles dans toutes les pages
$adminName = htmlspecialchars($adminData['username']);
$adminRole = htmlspecialchars($adminData['role']);
$isSuperAdmin = ($adminData['role'] === 'superadmin');

// Fonction utilitaire pour vérifier les rôles
function requireSuperAdmin()
{
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
        header('Location: dashboard.php?error=access_denied');
        exit();
    }
}
