<?php
session_start();
include 'elements/header.php';
include 'connexion.php';

// recupere le niveau de difficulté grace a la session
$difficultyLevel = $_SESSION['difficultyLevel'];

// initialiser les variables
$playerCreated = false;
$try = isset($_SESSION['try']) ? $_SESSION['try'] : 8;
$gameResult = isset($_SESSION['gameResult']) ? $_SESSION['gameResult'] : null;

// verifie si un mot a deja ete choisis
if (isset($_SESSION['randomWord'])) {
    $randomWord = $_SESSION['randomWord'];
    $hiddenWordArray = $_SESSION['hiddenWordArray'];
    $hiddenWordString = $_SESSION['hiddenWordString'];
} else {
    // genere un nouveau mot si aucun mot n'a ete genere
    if ($difficultyLevel == "easy") {
        $min_length = 0;
        $max_length = 6;
    } elseif ($difficultyLevel == "medium") {
        $min_length = 6;
        $max_length = 8;
    } elseif ($difficultyLevel == "hard") {
        $min_length = 8;
        $max_length = 25;
    }
    $stmt = $mysqlClient->prepare('SELECT w.wrd_word FROM words w WHERE CHAR_LENGTH(w.wrd_word) <= ? and char_length(w.wrd_word) > ?');
    $stmt->execute([$max_length, $min_length]);
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // genere un index aleatoire pour choisir un mot aleatoire
    $randomIndex = array_rand($words);

    // recupere le mot
    $randomWord = $words[$randomIndex]['wrd_word'];
    $randomWord = iconv('UTF-8', 'ASCII//TRANSLIT', $randomWord); // enleve les caracteres speciaux
    $randomWord = strtoupper($randomWord); // convertir le mot en majuscule

    // tableau d'underscore
    $hiddenWordArray = array_fill(0, strlen($randomWord), '_');

    // transforme le tableau en chaine de caractere
    $hiddenWordString = implode(" ", $hiddenWordArray);

    // stocke le mot choisi et le tableau d'underscore dans la session
    $_SESSION['randomWord'] = $randomWord;
    $_SESSION['hiddenWordArray'] = $hiddenWordArray;
    $_SESSION['hiddenWordString'] = $hiddenWordString;
}

// demande le nom et stocke le nom dans la bdd
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['playerName'])) {
    $playerName = $_POST['playerName'];

    // verifie si le joueur existe deja
    if (!empty($playerName)) {
        $stmt = $mysqlClient->prepare("SELECT pla_id FROM players WHERE pla_name = ?");
        $stmt->execute([$playerName]);
        $playerId = $stmt->fetchColumn();

        if ($playerId) {
            // le joueur existe deja, on recupere son ID
            $_SESSION['playerId'] = $playerId;
            $playerCreated = true;
        } else {
            // insere le joueur en bdd
            $stmt = $mysqlClient->prepare("INSERT INTO players (pla_name) VALUES (?)");
            $stmt->execute([$playerName]);
            $playerId = $mysqlClient->lastInsertId();
            $_SESSION['playerId'] = $playerId;
            $playerCreated = true;
        }
    } else {
        echo "Player name cannot be empty!";
    }
}

// tableau pour stocker les lettres utilisees
$lettersTried = isset($_SESSION['lettersTried']) ? $_SESSION['lettersTried'] : array();

// verifie les lettres entrees
if (isset($_POST['guessedLetter'])) {
    $guessedLetter = strtoupper($_POST['guessedLetter']);
    // verifie si la lettre a deja ete utilisee
    if (in_array($guessedLetter, $lettersTried)) {
    } else {
        $lettersTried[] = $guessedLetter;
        // verifie si la lettre est presente dans le mot
        if (strpos($randomWord, $guessedLetter) !== false) {
            // met a jour le tableau d'underscore
            for ($i = 0; $i < strlen($randomWord); $i++) {
                if ($randomWord[$i] == $guessedLetter) {
                    $hiddenWordArray[$i] = $guessedLetter;
                }
            }
        } else {
            // met a jour le nombre d'essais
            $try--;
        }
        $hiddenWordString = implode(" ", $hiddenWordArray);

        // met a jour les variables de session
        $_SESSION['hiddenWordArray'] = $hiddenWordArray;
        $_SESSION['hiddenWordString'] = $hiddenWordString;
        $_SESSION['try'] = $try;
        $_SESSION['lettersTried'] = $lettersTried;
    }
}


// victoire
if (!in_array('_', $hiddenWordArray)) {
    $gameResult = "win";
    $_SESSION['gameResult'] = $gameResult;
}

// defaite
if ($try === 0) {
    $gameResult = "lose";
    $_SESSION['gameResult'] = $gameResult;
}

// fonction pour envoyer les variables en bdd
function saveGameData($winStatus, $difficultyLevel, $playerId, $date)
{
    global $mysqlClient;

    // Prepare and execute the SQL statement to insert game data into the database
    $stmt = $mysqlClient->prepare("INSERT INTO player_stats (pls_win, pls_difficulty, pla_id, pls_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$winStatus, $difficultyLevel, $playerId, $date]);
    $stmt->closeCursor();

    return true;
}

// relancer une partie et reinitialiser la session
function restartGame()
{
    global $mysqlClient;
    $date = date('Y-m-d H:i:s');
    $playerId = isset($_SESSION['playerId']) ? $_SESSION['playerId'] : null;
    if ($_SESSION['gameResult'] === "win" || $_SESSION['gameResult'] === "lose") {
        saveGameData($_SESSION['gameResult'], $_SESSION['difficultyLevel'], $playerId, $date);
    }

    unset($_SESSION['randomWord']);
    unset($_SESSION['hiddenWordArray']);
    unset($_SESSION['hiddenWordString']);
    unset($_SESSION['try']);
    unset($_SESSION['gameResult']);
    unset($_SESSION['difficultyLevel']);
    unset($_SESSION['playerName']);
    unset($_SESSION['playerId']);
    unset($_SESSION['lettersTried']);

    header("Location: home.php");
    exit();
}

// fonction pour dessiner le pendu en fonction du nombre de tentatives restantes
function drawHangman($triesLeft)
{
    switch ($triesLeft) {
        case 8:
            echo "<pre>
      </pre>";
            break;
        case 7:
            echo "<pre>
                    _______
                   |       |
                   |       
                   |      
                   |        
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 6:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      
                   |        
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 5:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |       |
                   |        
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 4:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      /|
                   |        
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 3:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      /|\
                   |        
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 2:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      /|\
                   |      / 
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 1:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      /|\
                   |      / \
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
        case 0:
            echo "<pre>
                    _______
                   |       |
                   |       O
                   |      /|\
                   |      / \
                   |       
                 __|__
                |     |______
                |            |
                |____________|
              </pre>";
            break;
    }
}

// verifie si le bouton pour relancer la partie a ete clique
if (isset($_POST['restart']) && $_POST['restart'] === "true") {
    restartGame();
}
?>

<?php if (!$playerCreated) : ?>
    <!-- choisir nom joueur -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="playerName" class="form-label">Entrez votre nom</label>
        <input type="text" class="form-control" id="playerName" name="playerName" required>
        <input type="hidden" name="difficultyLevel" value="<?php echo htmlspecialchars($difficultyLevel); ?>">
        <button type="submit" class="btn btn-primary mt-2">Lancer la partie</button>
    </form>
<?php elseif (isset($gameResult) && ($gameResult === "win" || $gameResult === "lose")) : ?>
    <!-- afficher victoire ou defaite -->
    <h1><?php echo $gameResult === "win" ? "Félicitations!" : "Game Over!"; ?></h1>
    <p><?php echo $gameResult === "win" ? "Vous avez deviné le mot: $randomWord" : "Désolé vous n'avez plus d'essais restants. Le mot était: $randomWord"; ?></p>
    <!-- bouton pour relancer la partie -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="restart" value="true">
        <button type="submit" class="btn btn-primary">Commencer une nouvelle partie</button>
    </form>
<?php else : ?>
    <div class="displayGame">
        <?php
        echo "Niveau de difficulté: " . $difficultyLevel . "<br>";
        echo "Essais restants: " . $try . "<br>";
        echo $hiddenWordString;
        // dessine le pendu en fonction du nombre de tentatives restantes
        drawHangman($try); ?>
        <!-- champ pour entrer une lettre -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="playerName" value="<?php echo htmlspecialchars($playerName); ?>">
            <input type="hidden" name="difficultyLevel" value="<?php echo htmlspecialchars($difficultyLevel); ?>">
            <input type="text" name="guessedLetter" placeholder="Enter a letter">
            <button type="submit">Entrer</button>
        </form>
    </div>
<?php endif; ?>

<?php include 'elements/footer.php'; ?>