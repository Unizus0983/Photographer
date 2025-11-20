<?php
session_start();
echo "<pre>";
echo "SESSION DATA:\n";
print_r($_SESSION);
echo "</pre>";

// Testez votre connexion
if (isset($_SESSION['admin_id'])) {
    echo "<p style='color:green'>✅ Admin ID: " . $_SESSION['admin_id'] . "</p>";
    echo "<p style='color:green'>✅ Admin Name: " . ($_SESSION['admin_name'] ?? 'Non défini') . "</p>";
    echo "<p style='color:green'>✅ Admin Role: " . ($_SESSION['admin_role'] ?? 'Non défini') . "</p>";
} else {
    echo "<p style='color:red'>❌ Aucune session admin trouvée</p>";
}
