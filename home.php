<?php
include 'elements/header.php';
include 'connexion.php';

@$submit =  $_GET['submit'];
@$difficultyLevel = $_GET['difficultyLevel'];


// Vérifiez si le formulaire est soumis et que le niveau de difficulté est sélectionné
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($submit) && isset($difficultyLevel)) {
    // Redirige vers la même page mais en passant le niveau de difficulté dans l'URL
    header("Location: game.php?difficultyLevel=$difficultyLevel");
    exit();
}



// Récupérez le niveau de difficulté de l'URL si disponible
if (isset($_GET['difficultyLevel'])) {
    $difficultyLevel = $_GET['difficultyLevel'];

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
}

?>
<div class="difficulty-container">
    <h1>Choose the difficulty of the game</h1>
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <button name="difficultyLevel" value="easy" type="submit">easy</button>
        <button name="difficultyLevel" value="medium" type="submit">medium</button>
        <button name="difficultyLevel" value="hard" type="submit">hard</button>
        <input type="hidden" name="submit" value="true"> <!-- Hidden input to submit form -->
    </form>
</div>

<?php



include 'elements/footer.php';
