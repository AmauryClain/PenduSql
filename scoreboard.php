<?php
include 'elements/header.php';
include 'connexion.php';
// trier par nom
$stmt = $mysqlClient->prepare("SELECT players.pla_name, player_stats.pls_difficulty, COUNT(player_stats.pls_win) AS total_wins
    FROM player_stats 
    JOIN players ON player_stats.pla_id = players.pla_id 
    WHERE player_stats.pls_win = 'win'
    GROUP BY player_stats.pla_id, player_stats.pls_difficulty, players.pla_name
    ORDER BY players.pla_name ASC
    limit 10");
$stmt->execute();
$scoresName = $stmt->fetchAll(PDO::FETCH_ASSOC);
// trier par nombre de parties gagnees
$stmt = $mysqlClient->prepare("SELECT players.pla_name, player_stats.pls_difficulty, COUNT(player_stats.pls_win) AS total_wins
    FROM player_stats 
    JOIN players ON player_stats.pla_id = players.pla_id 
    WHERE player_stats.pls_win = 'win'
    GROUP BY player_stats.pla_id, player_stats.pls_difficulty, players.pla_name
    ORDER BY total_wins DESC
    limit 10");
$stmt->execute();
$scoresWin = $stmt->fetchAll(PDO::FETCH_ASSOC);

// trier par niveau de difficulte (Difficile en premier, moyen en second et facile en 3eme) puis par score décroissant
$stmt = $mysqlClient->prepare("SELECT players.pla_name, player_stats.pls_difficulty, COUNT(player_stats.pls_win) AS total_wins
    FROM player_stats 
    JOIN players ON player_stats.pla_id = players.pla_id 
    WHERE player_stats.pls_win = 'win'
    GROUP BY player_stats.pla_id, player_stats.pls_difficulty, players.pla_name
    ORDER BY 
        CASE player_stats.pls_difficulty 
            WHEN 'hard' THEN 1 
            WHEN 'medium' THEN 2 
            ELSE 3 
        END,
        total_wins DESC
        limit 10");
$stmt->execute();
$scoresDiff = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<a class="btn btn-primary" href="home.php" role="button">Retour</a>

<div class="form-check form-switch " id="namediv">
    <input class="form-check-input" type="checkbox" role="switch" name="nameCheck" id="nameCheck" onclick="nameCheck()" checked>
    <label class="form-check-label" for="nameCheck">Tri par nom</label>
</div>
<div class="form-check form-switch" id="windiv">
    <input class="form-check-input" type="checkbox" role="switch" name="winCheck" id="winCheck" onclick="winCheck()">
    <label class="form-check-label" for="winCheck">Tri par score</label>
</div>
<div class="form-check form-switch" id="diffdiv">
    <input class="form-check-input" type="checkbox" role="switch" name="diffCheck" id="diffCheck" onclick="diffCheck()">
    <label class="form-check-label" for="diffCheck" name="diffCheck" type="submit">Tri par difficulté</label>
</div>


<!-- tableau par nom -->
<table id="nameTable" class="table">
    <thead>
        <tr>
            <th>Nom du joueur</th>
            <th>Difficulté</th>
            <th>Nombre de Parties gagnées</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($scoresName as $score) : ?>
            <tr>
                <td><?php echo $score['pla_name']; ?></td>
                <td><?php echo $score['pls_difficulty']; ?></td>
                <td><?php echo $score['total_wins']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- tableau par wins -->
<table style="display: none;" id="winTable" class="table">
    <thead>
        <tr>
            <th>Nom du joueur</th>
            <th>Difficulté</th>
            <th>Nombre de Parties gagnées</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($scoresWin as $score) : ?>
            <tr>
                <td><?php echo $score['pla_name']; ?></td>
                <td><?php echo $score['pls_difficulty']; ?></td>
                <td><?php echo $score['total_wins']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- tableau par niveau de difficulte -->
<table style="display: none;" id="diffTable" class="table">
    <thead>
        <tr>
            <th>Nom du joueur</th>
            <th>Difficulté</th>
            <th>Nombre de Parties gagnées</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($scoresDiff as $score) : ?>
            <tr>
                <td><?php echo $score['pla_name']; ?></td>
                <td><?php echo $score['pls_difficulty']; ?></td>
                <td><?php echo $score['total_wins']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    function nameCheck() {
        // checkbox
        var checkBox = document.getElementById("nameCheck");
        // affichage
        var form = document.getElementById("nameTable");
        // éléments à cacher
        var memberCheck = document.getElementById("winCheck");
        var albumCheck = document.getElementById("diffCheck");
        var memberform = document.getElementById("winTable");
        var albumform = document.getElementById("diffTable");

        if (checkBox.checked == true) {
            form.style.display = "block";
            memberCheck.checked = false;
            albumCheck.checked = false;
            memberform.style.display = "none";
            albumform.style.display = "none";

        } else {
            form.style.display = "none";
        }
    }

    function winCheck() {
        // checkbox
        var checkBox = document.getElementById("winCheck");
        // affichage
        var form = document.getElementById("winTable");
        // éléments à cacher
        var groupCheck = document.getElementById("nameCheck");
        var albumCheck = document.getElementById("diffCheck");
        var groupform = document.getElementById("nameTable");
        var albumform = document.getElementById("diffTable");

        if (checkBox.checked == true) {
            form.style.display = "block";
            groupCheck.checked = false;
            albumCheck.checked = false;
            groupform.style.display = "none";
            albumform.style.display = "none";

        } else {
            form.style.display = "none";
        }
    }

    function diffCheck() {
        // checkbox
        var checkBox = document.getElementById("diffCheck");
        // affichage
        var form = document.getElementById("diffTable");
        // éléments à cacher
        var groupCheck = document.getElementById("nameCheck");
        var memberCheck = document.getElementById("winCheck");
        var groupform = document.getElementById("nameTable");
        var memberform = document.getElementById("winTable");

        if (checkBox.checked == true) {
            form.style.display = "block";
            groupCheck.checked = false;
            memberCheck.checked = false;
            groupform.style.display = "none";
            memberform.style.display = "none";

        } else {
            form.style.display = "none";
        }
    }
</script>

<?php


include 'elements/footer.php';
