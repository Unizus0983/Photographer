<?php
function checkAdminAuth()
{
    // Démarre la session seulement si pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: connexion_admin.php');
        exit();
    }
    return true;
}

function requireSuperAdmin()
{
    // Vérifier d'abord que l'admin est connecté
    checkAdminAuth();

    // Ensuite vérifier le rôle
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
        header('Location: dashboard.php?error=access_denied');
        exit();
    }

    return true;
}
