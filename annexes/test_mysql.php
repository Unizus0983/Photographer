<?php
header('Content-Type: text/html');
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8', 'root', '');
    echo "✅ MySQL CONNECTÉ !<br>";

    // Testez si votre base existe
    $bdd = new PDO('mysql:host=localhost;dbname=exos;charset=utf8', 'root', '');
    echo "✅ Base 'exos' ACCESSIBLE !<br>";

    // Testez les articles
    $req = $bdd->query('SELECT COUNT(*) as total FROM articles');
    $result = $req->fetch();
    echo "✅ " . $result['total'] . " articles trouvés !";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
