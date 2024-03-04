<!-- Afficher le score de 3 manières différentes :
- ordre alph des noms de joueurs
- par score décroissant
-Par niveau de difficulté (Difficile en premier, moyen en second et facile en 3ème) puis par score 
décroissant
afficher : nom, difficulté, nombre de parties gg en fonction de la difficulté -->
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
// trier par nombre de parties gagnées
$stmt = $mysqlClient->prepare("SELECT players.pla_name, player_stats.pls_difficulty, COUNT(player_stats.pls_win) AS total_wins
    FROM player_stats 
    JOIN players ON player_stats.pla_id = players.pla_id 
    WHERE player_stats.pls_win = 'win'
    GROUP BY player_stats.pla_id, player_stats.pls_difficulty, players.pla_name
    ORDER BY total_wins DESC
    limit 10");
$stmt->execute();
$scoresWin = $stmt->fetchAll(PDO::FETCH_ASSOC);

// trier par niveau de difficulté (Difficile en premier, moyen en second et facile en 3ème) puis par score décroissant
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

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="alphabeticalCheckbox" checked>
    <label class="form-check-label" for="alphabeticalCheckbox">
        Afficher/dissimuler l'ordre alphabétique des noms de joueurs
    </label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="scoreCheckbox" checked>
    <label class="form-check-label" for="scoreCheckbox">
        Afficher/dissimuler le score décroissant
    </label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="difficultyCheckbox" checked>
    <label class="form-check-label" for="difficultyCheckbox">
        Afficher/dissimuler par niveau de difficulté
    </label>
</div>
<!-- Tableau alphabétique -->
<table id="alphabeticalTable" class="table">
    <thead>
        <p>Tri par nom de joueur</p>
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

<!-- Tableau de score -->
<table id="scoreTable" class="table">
    <thead>
        <p>Tri par score</p>
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

<!-- Tableau par niveau de difficulté -->
<table id="difficultyTable" class="table">
    <thead>
        <p>Tri par niveau de difficulté</p>
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

<?php


include 'elements/footer.php';
