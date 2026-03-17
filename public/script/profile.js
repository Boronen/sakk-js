const params = new URLSearchParams(window.location.search);
let profilUser = params.get("user");

const loggedInUser = localStorage.getItem("felhasznalo");
if (!profilUser) profilUser = loggedInUser;

const isOwnProfile = profilUser === loggedInUser;

const wrapper = document.querySelector(".profile-wrapper");
const headerBackground = document.querySelector("#headerBackground");
const profilKep = document.querySelector("#profilKep");
const statAdatok = document.querySelector("#statAdatok");
const profilNev = document.querySelector("#profilNev");
const meccsTabla = document.querySelector("#meccsTabla tbody");
const badgeLista = document.querySelector("#badgeLista");

let aktualisProfilAdat = null; // ide eltesszük a profil adatot, hogy a puzzle is elérje

function betoltProfil() {
  fetch(`../backend/profile.php?user=${profilUser}`)
    .then((r) => r.json())
    .then((adat) => {
      if (adat.status !== "ok") return;

      aktualisProfilAdat = adat;

      profilNev.textContent = adat.user;

      statAdatok.innerHTML = `
        <p>ELO: ${adat.elo}</p>
        <p>Win: ${adat.win}</p>
        <p>Lose: ${adat.lose}</p>
        <p>Winrate: ${adat.winrate}%</p>
      `;

      profilKep.src = `assets/pfp/${adat.pfp}`;

      if (adat.header_bg)
        headerBackground.style.backgroundImage = `url('assets/header_backgrounds/${adat.header_bg}')`;

      if (adat.background_style !== "default")
        wrapper.style.backgroundImage = `url('assets/backgrounds/${adat.background_style}')`;

      badgeLista.innerHTML = "";
      adat.badges.forEach((b) => {
        const img = document.createElement("img");
        img.src = `assets/badges/${b.file}`;
        img.classList.add("badge");
        img.classList.add(b.rarity);
        img.title = `${b.name}\n${b.description}`;
        badgeLista.appendChild(img);
      });

      meccsTabla.innerHTML = "";
      adat.matches.forEach((m) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${m.game_id}</td>
          <td><a href="profile.html?user=${m.white}">${m.white}</a></td>
          <td><a href="profile.html?user=${m.black}">${m.black}</a></td>
          <td>${m.winner}</td>
        `;
        meccsTabla.appendChild(tr);
      });

      // TITKOS PUZZLE FELAJÁNLÁSA
      if (adat.win >= 10 && isOwnProfile) {
        console.log(
          "%cTitkos kihívás elérhető a profilodon!",
          "color: #ff00ff; font-size: 18px; font-weight: bold;"
        );
        console.log(
          "%cÍrd be a konzolba: startElectionPuzzle()",
          "color: #ff00ff; font-size: 14px;"
        );
      }
    });
}

betoltProfil();

window.addEventListener("load", () => {
  if (!isOwnProfile) {
    const pfpUpload = document.querySelector("#pfpUpload");
    const headerBgUpload = document.querySelector("#headerBgUpload");

    if (pfpUpload) pfpUpload.style.display = "none";
    if (headerBgUpload) headerBgUpload.style.display = "none";

    document.querySelectorAll(".bg-btn").forEach((btn) => {
      btn.disabled = true;
      btn.style.opacity = "0.5";
    });
  }
});

const pfpUploadInput = document.querySelector("#pfpUpload");
if (pfpUploadInput) {
  pfpUploadInput.addEventListener("change", (e) => {
    if (!isOwnProfile) return;

    const file = e.target.files[0];
    const form = new FormData();
    form.append("file", file);
    form.append("user", profilUser);

    fetch("../backend/upload_pfp.php", { method: "POST", body: form })
      .then((r) => r.json())
      .then(() => betoltProfil());
  });
}

const headerBgUploadInput = document.querySelector("#headerBgUpload");
if (headerBgUploadInput) {
  headerBgUploadInput.addEventListener("change", (e) => {
    if (!isOwnProfile) return;

    const file = e.target.files[0];
    const form = new FormData();
    form.append("file", file);
    form.append("user", profilUser);

    fetch("../backend/upload_header.php", { method: "POST", body: form })
      .then((r) => r.json())
      .then(() => betoltProfil());
  });
}

document.querySelectorAll(".bg-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    if (!isOwnProfile) return;

    const form = new FormData();
    form.append("user", profilUser);
    form.append("style", btn.dataset.bg);

    fetch("../backend/save_background_style.php", {
      method: "POST",
      body: form,
    })
      .then((r) => r.json())
      .then(() => betoltProfil());
  });
});

// ===== TITKOS KONZOLOS REJTVÉNY =====

window.startElectionPuzzle = function () {
  if (!aktualisProfilAdat) {
    console.log("Előbb töltsd be a profilt.");
    return;
  }

  if (!isOwnProfile) {
    console.log("Ez a kihívás csak a saját profilodon érhető el.");
    return;
  }

  if (aktualisProfilAdat.win < 10) {
    console.log("Legalább 10 győzelem kell a titkos kihíváshoz.");
    return;
  }

  console.log(
    "%cRejtvény:",
    "color: yellow; font-size: 18px; font-weight: bold;"
  );
  console.log(
    "Egy magyar választáson a szavazók 60%-a jelent meg. A győztes párt a leadott szavazatok 52%-át kapta."
  );
  console.log(
    "Kérdés: a választásra jogosultak hány százaléka szavazott a győztesre?"
  );
  console.log(
    "%cÍrd be: solveElectionPuzzle(valasz)",
    "color: yellow; font-size: 14px;"
  );
};

window.solveElectionPuzzle = function (v) {
  if (!aktualisProfilAdat) {
    console.log("Előbb töltsd be a profilt.");
    return;
  }

  if (!isOwnProfile) {
    console.log("Ez a kihívás csak a saját profilodon érhető el.");
    return;
  }

  const goodAnswers = ["31.2", "31.2%", "0.312", 31.2, 0.312];

  if (!goodAnswers.includes(v)) {
    console.log("%cRossz válasz. Próbáld újra!", "color: red; font-size: 14px;");
    return;
  }

  console.log(
    "%cHelyes! Megkapod a titkos badge-et!",
    "color: #00ff00; font-size: 18px; font-weight: bold;"
  );

  const form = new FormData();
  form.append("user", profilUser);
  form.append("badge", "secret_election_badge.gif");

  fetch("../backend/give_badge.php", {
    method: "POST",
    body: form,
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status === "ok") {
        console.log(
          "%cBadge hozzáadva a profilodhoz! Frissítsd az oldalt, hogy lásd.",
          "color: #00ff00; font-size: 14px;"
        );
        betoltProfil();
      } else {
        console.log("Hiba a badge kiosztásakor:", res.message);
      }
    })
    .catch(() => {
      console.log("Hálózati hiba a badge kiosztásakor.");
    });
};
