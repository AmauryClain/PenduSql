function getDifficulty(level) {
  let levelDifficulty = level;
  console.log(level);
  return levelDifficulty;
}

// Fonction pour afficher ou cacher un tableau en fonction de l'état de la case à cocher
function toggleTable(tableId, checkboxId) {
  const table = document.getElementById(tableId);
  const checkbox = document.getElementById(checkboxId);
  table.style.display = checkbox.checked ? "table" : "none";
}

// Écouteurs d'événements pour chaque case à cocher
document
  .getElementById("alphabeticalCheckbox")
  .addEventListener("change", function () {
    toggleTable("alphabeticalTable", "alphabeticalCheckbox");
  });

document
  .getElementById("scoreCheckbox")
  .addEventListener("change", function () {
    toggleTable("scoreTable", "scoreCheckbox");
  });

document
  .getElementById("difficultyCheckbox")
  .addEventListener("change", function () {
    toggleTable("difficultyTable", "difficultyCheckbox");
  });
