<?php
include 'elements/header.php';
include 'connexion.php';

// Récupérez le niveau de difficulté de l'URL
$difficultyLevel = $_GET['difficultyLevel'] ?? '';

// Affichez le contenu en fonction du niveau de difficulté récupéré
if ($difficultyLevel == "easy") {
    include 'connexion.php';
    $res = $mysqlClient->prepare('SELECT w.wrd_word FROM words w WHERE CHAR_LENGTH(w.wrd_word) <= 4');
    $res->execute();
    $words = $res->fetchAll();
    foreach ($words as $word) {
        echo $word['wrd_word'];
    }
} elseif ($difficultyLevel == "medium") {
    include 'connexion.php';
    $res = $mysqlClient->prepare('SELECT w.wrd_word FROM words w WHERE CHAR_LENGTH(w.wrd_word) <= 6 AND CHAR_LENGTH(w.wrd_word) > 4');
    $res->execute();
    $words = $res->fetchAll();
    foreach ($words as $word) {
        echo $word['wrd_word'];
    }
} elseif ($difficultyLevel == "hard") {
    include 'connexion.php';
    $res = $mysqlClient->prepare('SELECT w.wrd_word FROM words w WHERE CHAR_LENGTH(w.wrd_word) >= 8');
    $res->execute();
    $words = $res->fetchAll();
    foreach ($words as $word) {
        echo $word['wrd_word'];
    }
}
?>

<?php
include 'elements/footer.php';
?>