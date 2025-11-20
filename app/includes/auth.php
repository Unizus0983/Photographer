<?php
function checkAdminAuth()
{
    session_start();

    // Vérifier si l'admin est connecté
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
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
