<?php
session_start();
include 'elements/header.php';
include 'connexion.php';

// Retrieve the difficulty level from the session
$difficultyLevel = $_SESSION['difficultyLevel'];

// Initialize variables
$playerCreated = false;
$try = isset($_SESSION['try']) ? $_SESSION['try'] : 8;
$gameResult = isset($_SESSION['gameResult']) ? $_SESSION['gameResult'] : null;

// Check if the random word is already generated and stored in the session
if (isset($_SESSION['randomWord'])) {
    $randomWord = $_SESSION['randomWord'];
    $hiddenWordArray = $_SESSION['hiddenWordArray'];
    $hiddenWordString = $_SESSION['hiddenWordString'];
} else {
    // Generate a new random word if not already generated
    if ($difficultyLevel == "easy") {
        $max_length = 6;
    } elseif ($difficultyLevel == "medium") {
        $max_length = 8;
    } elseif ($difficultyLevel == "hard") {
        $max_length = 25;
    }

    $stmt = $mysqlClient->prepare('SELECT w.wrd_word FROM words w WHERE CHAR_LENGTH(w.wrd_word) <= ?');
    $stmt->execute([$max_length]);
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate a random index to choose a random word
    $randomIndex = array_rand($words);

    // Retrieve the random word and clean it
    $randomWord = $words[$randomIndex]['wrd_word'];
    $randomWord = iconv('UTF-8', 'ASCII//TRANSLIT', $randomWord); // Remove accents and special characters
    $randomWord = strtoupper($randomWord); // Convert to uppercase

    // Create an array to store the hidden word
    $hiddenWordArray = array_fill(0, strlen($randomWord), '_');

    // Convert the hidden word array to string
    $hiddenWordString = implode(" ", $hiddenWordArray);

    // Store the random word and hidden word array in the session
    $_SESSION['randomWord'] = $randomWord;
    $_SESSION['hiddenWordArray'] = $hiddenWordArray;
    $_SESSION['hiddenWordString'] = $hiddenWordString;
}

// Process the form to save the player name in the database and start the game
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['playerName'])) {
    $playerName = $_POST['playerName'];

    // Check if the player name is not empty
    if (!empty($playerName)) {
        // Check if the player name already exists in the database
        $stmt = $mysqlClient->prepare("SELECT pla_id FROM players WHERE pla_name = ?");
        $stmt->execute([$playerName]);
        $playerId = $stmt->fetchColumn();

        if ($playerId) {
            // Player already exists, retrieve the player ID
            $_SESSION['playerId'] = $playerId;
            $playerCreated = true;
        } else {
            // Insert the player name into the database
            $stmt = $mysqlClient->prepare("INSERT INTO players (pla_name) VALUES (?)");
            $stmt->execute([$playerName]);
            $playerId = $mysqlClient->lastInsertId();
            $_SESSION['playerId'] = $playerId;
            $playerCreated = true;
        }
    } else {
        // Player name is empty, handle accordingly (e.g., display an error message)
        echo "Player name cannot be empty!";
    }
}

// Check if a letter has been guessed
if (isset($_POST['guessedLetter'])) {
    $guessedLetter = strtoupper($_POST['guessedLetter']);

    // Check if the guessed letter is present in the random word
    if (strpos($randomWord, $guessedLetter) !== false) {
        // Update the hidden word array to reveal the correctly guessed letters
        for ($i = 0; $i < strlen($randomWord); $i++) {
            if ($randomWord[$i] == $guessedLetter) {
                $hiddenWordArray[$i] = $guessedLetter;
            }
        }
    } else {
        // Decrease the number of remaining tries if the guessed letter is incorrect
        $try--;
    }

    // Convert the updated hidden word array to string
    $hiddenWordString = implode(" ", $hiddenWordArray);

    // Update the session variables
    $_SESSION['hiddenWordArray'] = $hiddenWordArray;
    $_SESSION['hiddenWordString'] = $hiddenWordString;
    $_SESSION['try'] = $try;
}

// Check for winning condition
if (!in_array('_', $hiddenWordArray)) {
    // Player has guessed all letters correctly
    $gameResult = "win";
    $_SESSION['gameResult'] = $gameResult;
}

// Check for losing condition
if ($try === 0) {
    // Player has run out of tries
    $gameResult = "lose";
    $_SESSION['gameResult'] = $gameResult;
}

// Function to save game data
function saveGameData($winStatus, $difficultyLevel, $playerId, $date)
{
    global $mysqlClient;

    // Prepare and execute the SQL statement to insert game data into the database
    $stmt = $mysqlClient->prepare("INSERT INTO player_stats (pls_win, pls_difficulty, pla_id, pls_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$winStatus, $difficultyLevel, $playerId, $date]);
    $stmt->closeCursor();

    return true;
}

// Function to reset session variables and redirect to home.php
function restartGame()
{
    global $mysqlClient;

    // Get current date and time
    $date = date('Y-m-d H:i:s');

    // Retrieve player ID from session
    $playerId = isset($_SESSION['playerId']) ? $_SESSION['playerId'] : null;

    // Save game data to the database before resetting session
    if ($_SESSION['gameResult'] === "win" || $_SESSION['gameResult'] === "lose") {
        saveGameData($_SESSION['gameResult'], $_SESSION['difficultyLevel'], $playerId, $date);
    }

    // Unset session variables
    unset($_SESSION['randomWord']);
    unset($_SESSION['hiddenWordArray']);
    unset($_SESSION['hiddenWordString']);
    unset($_SESSION['try']);
    unset($_SESSION['gameResult']);
    unset($_SESSION['difficultyLevel']);
    unset($_SESSION['playerName']);
    unset($_SESSION['playerId']);

    // Redirect to home.php
    header("Location: home.php");
    exit();
}

// Check if the restart button was clicked
if (isset($_POST['restart']) && $_POST['restart'] === "true") {
    // Reset session variables
    restartGame();
}
?>

<?php if (!$playerCreated) : ?>
    <!-- Player name form -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="playerName" class="form-label">Enter your name</label>
        <input type="text" class="form-control" id="playerName" name="playerName" required>
        <input type="hidden" name="difficultyLevel" value="<?php echo htmlspecialchars($difficultyLevel); ?>">
        <button type="submit" class="btn btn-primary mt-2">Start the game</button>
    </form>
<?php elseif (isset($gameResult) && ($gameResult === "win" || $gameResult === "lose")) : ?>
    <!-- Display winning or losing message -->
    <h1><?php echo $gameResult === "win" ? "Congratulations!" : "Game Over!"; ?></h1>
    <p><?php echo $gameResult === "win" ? "You've guessed the word correctly: $randomWord" : "Sorry, you've run out of tries. The word was: $randomWord"; ?></p>
    <!-- Restart game button -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="restart" value="true">
        <button type="submit" class="btn btn-primary">Restart Game</button>
    </form>
<?php else : ?>
    <!-- Display the game interface -->
    <h1>Test</h1>
    <?php
    echo "Player Name: " . $playerName . "<br>";
    echo "Difficulty Level: " . $difficultyLevel . "<br>";
    echo "Random Word: " . $randomWord . "<br>";
    echo "Remaining Tries: " . $try . "<br>";
    echo "Hidden Word: " . $hiddenWordString;
    ?>
    <!-- Add game UI elements here -->
    <div class="displayGame">
        <!-- Input field for the player to guess a letter -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="playerName" value="<?php echo htmlspecialchars($playerName); ?>">
            <input type="hidden" name="difficultyLevel" value="<?php echo htmlspecialchars($difficultyLevel); ?>">
            <input type="text" name="guessedLetter" placeholder="Enter a letter">
            <button type="submit">Guess</button>
        </form>
    </div>
<?php endif; ?>

<?php include 'elements/footer.php'; ?>