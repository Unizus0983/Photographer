<?php
session_start();

// On supprime seulement les variables liées à l'admin
unset($_SESSION['admin']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);


// Redirection vers la page de connexion ADMIN avec message
header('Location: connexion_admin.php?logout=1');
exit();
exit;
