<?php

//permet de renvoyer les données qui seront appelés en js 
header('Content-Type: application/json');
// Pour permettre à n'importe quelle ressource d'accéder à vos ressources car pas d'adresse du serveur pour les données hormis localhost, site non connecté à une base de données hebergée
header('Access-Control-Allow-Origin: *');
require_once '../includes/connect.php';

//récupérations de la page demandée
// Syntaxe: condition ? valeur_si_vrai : valeur_si_faux =>opérateur ternaire
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
//pour ne pas avoir de page <0
if ($page < 1) {
    $page = 1;
}

$articlesPerPage = 3;
//conversion page utilisateur en position sql  : pont vers base de données articles [0,1,2,3] 
$begin = ($page - 1) * $articlesPerPage;



try {
    //total des articles 
    $requete = $pdo->query('SELECT COUNT(*) FROM articles');
    $totalArticles = $requete->fetchColumn();
    //nombre de page à raison des 3 articles par page par apport au nombres d'articles existant
    $totalPages = ceil($totalArticles / $articlesPerPage);

    //requete pour tous les élèments qui composent article par page

    $stmt = $pdo->prepare('SELECT `id_article`, `titre`, `contenu`, `date_publication`, `image`
                          FROM articles 
                          ORDER BY date_publication DESC 
                          LIMIT :debut, :limite');
    //  Associe une valeur à un paramètre =>binValue paramètre valeur type
    $stmt->bindValue(':debut', $begin, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $articlesPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $articles,
        'pagination' => [
            'current_page' => $page,
            'total_page' => $totalPages,
            'articles_per_page' => $articlesPerPage,
        ]

    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
