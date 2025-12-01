<?php
//constantes// ATTENTION : Configuration développement local seulement
// EN PRODUCTION : utiliser variables d'environnement

$host = 'localhost';
$dbname = 'photographer';
$user = 'root';
$password = '';
//connexion à la base
try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //on definit dés le début la connexion le mode de 'fetch' par défaut 
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    //echo 'connexion réussie !';

    // Requête SQL
    // $sqlVisitors = "SELECT * FROM `visiteurs`";

    // Préparation + Execution de la requête
    // $stmt = $pdo->prepare($sqlVisitors);
    // $stmt->execute();

    // Réponse - Exploitation des données
    // $visitors = $stmt->fetchAll();
    // echo " <pre>";
    // var_dump($visitors);
    // echo '</pre>';
} catch (PDOException $e) {
    die("erreur de connexion !" . $e->getMessage());
}
