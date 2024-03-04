<?php
session_start();
include 'elements/header.php';
include 'connexion.php';

@$submit =  $_GET['submit'];
@$difficultyLevel = $_GET['difficultyLevel'];
$_SESSION['difficultyLevel'] = $difficultyLevel;

// verifier si le formulaire est soumis et que le niveau de difficulte est sélectionne
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($submit) && isset($difficultyLevel)) {
    header("Location: game.php");
    exit();
}
?>
<div class="difficulty-container">
    <h1>Choose the difficulty of the game</h1>
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <button name="difficultyLevel" value="easy" type="submit">facile</button>
        <button name="difficultyLevel" value="medium" type="submit">moyen</button>
        <button name="difficultyLevel" value="hard" type="submit">difficile</button>
        <input type="hidden" name="submit" value="true">
    </form>
    <a class="btn btn-primary" href="scoreboard.php" role="button">Classement</a>
</div>


<?php



include 'elements/footer.php';
