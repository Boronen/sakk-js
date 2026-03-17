const tabla = document.querySelector("#leaderboardTabla tbody");

fetch("../backend/leaderboard.php")
    .then(r => r.json())
    .then(adat => {

        if (adat.status !== "ok") return;

        const lista = adat.leaderboard;

        tabla.innerHTML = "";

        lista.forEach((sor, index) => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${sor.user}</td>
                <td>${sor.elo}</td>
                <td>${sor.win}</td>
                <td>${sor.lose}</td>
                <td>${sor.winrate}%</td>
            `;

            tabla.appendChild(tr);
        });
    });
