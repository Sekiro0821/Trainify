document.addEventListener("DOMContentLoaded", function () {
    const addSetBtn = document.getElementById("add-set-btn");
    const setsContainer = document.getElementById("sets-container");

    // Kör bara koden om vi befinner oss på en sida som har dessa knappar (workouts.php)
    if (addSetBtn && setsContainer) {
        let setCount = 1;

        addSetBtn.addEventListener("click", function () {
            setCount++;

            // Skapa en ny <div> för det nya setet
            const setRow = document.createElement("div");
            setRow.className = "set-row";
            setRow.style.marginBottom = "10px";

            setRow.innerHTML = `
                <strong>Set ${setCount}:</strong>
                <label>Vikt (kg):</label>
                <input type="number" step="0.5" name="weights[]" min="0" required>

                <label>Reps:</label>
                <input type="number" name="reps[]" min="1" required>

                <button type="button" class="remove-set-btn">Ta bort</button>
            `;

            // Lägg in den nya raden på sidan
            setsContainer.appendChild(setRow);

            // Gör så att "Ta bort"-knappen tar bort just denna rad
            const removeBtn = setRow.querySelector(".remove-set-btn");
            removeBtn.addEventListener("click", function () {
                setRow.remove();
            });
        });
    }
});