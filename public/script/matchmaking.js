const keresGomb = document.querySelector("#keresJatekGomb");
const matchmakingUzenet = document.querySelector("#matchmakingUzenet");

let matchmakingInterval = null;
let redirecting = false;

if (keresGomb) {
  keresGomb.addEventListener("click", inditMatchmaking);
}

function inditMatchmaking() {
  const felhasznalo = localStorage.getItem("felhasznalo");
  if (matchmakingInterval !== null) {
    return;
  }
  if (!felhasznalo) {
    matchmakingUzenet.textContent = "Előbb jelentkezz be.";
    return;
  }

  matchmakingUzenet.textContent = "Játék keresése...";

  const adat = new FormData();
  adat.append("user", felhasznalo);

  fetch("../backend/join_matchmaking.php", {
    method: "POST",
    body: adat,
  })
    .then((r) => r.json())
    .then((valasz) => {
      if (valasz.status === "queued" || valasz.status === "ok") {
        if (!matchmakingInterval) {
          matchmakingInterval = setInterval(
            () => pollMatchmaking(felhasznalo),
            1000,
          );
        }
      } else {
        matchmakingUzenet.textContent =
          "Hiba: " + (valasz.message || "ismeretlen");
      }
    })
    .catch(() => {
      if (!redirecting) {
        matchmakingUzenet.textContent = "Hálózati hiba.";
      }
    });
}
function mutasdMatchPopup(gameId) {
  const popup = document.querySelector("#matchPopup");
  const joinBtn = document.querySelector("#joinMatchBtn");

  popup.style.display = "block";

  joinBtn.onclick = () => {
    window.location.href = `game.html?game=${gameId}`;
  };
}

function pollMatchmaking(felhasznalo) {
  const adat = new FormData();
  adat.append("user", felhasznalo);

  fetch("../backend/poll_matchmaking.php", {
    method: "POST",
    body: adat,
  })
    .then((r) => r.json())
    .then((valasz) => {
      if (valasz.status === "match_found") {
        clearInterval(matchmakingInterval);
        matchmakingInterval = null;

        matchmakingUzenet.textContent = "Ellenfél megtalálva!";

        mutasdMatchPopup(valasz.game);
        return;
      }

      if (valasz.status === "not_in_queue") {
        clearInterval(matchmakingInterval);
        matchmakingInterval = null;
        matchmakingUzenet.textContent = "Nem vagy már a várólistán.";
        return;
      }
    })
    .catch(() => {
      if (!redirecting) {
        matchmakingUzenet.textContent = "Hálózati hiba.";
      }
    });
}
