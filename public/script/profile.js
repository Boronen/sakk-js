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
const bgOptions = document.querySelector(".bg-options");

let aktualisProfilAdat = null; // ide eltesszük a profil adatot, hogy a puzzle is elérje

const SECRET_BADGE_FILE = "secret_election_badge.gif";
const SECRET_BACKGROUND_STYLE = "firev2.gif";

function betoltProfil() {
  return fetch(`../backend/profile.php?user=${profilUser}`)
    .then((r) => r.json())
    .then((adat) => {
      if (adat.status !== "ok") return;

      aktualisProfilAdat = adat;
      const isFireV2 = adat.background_style === SECRET_BACKGROUND_STYLE;
      const hasCustomBg = adat.background_style !== "default" && !isFireV2;

      profilNev.textContent = adat.user;

      statAdatok.innerHTML = `
        <p>ELO: ${adat.elo}</p>
        <p>Win: ${adat.win}</p>
        <p>Lose: ${adat.lose}</p>
        <p>Winrate: ${adat.winrate}%</p>
      `;

      profilKep.src = `assets/pfp/${adat.pfp}`;

      headerBackground.style.backgroundImage = adat.header_bg
        ? `url('assets/header_backgrounds/${adat.header_bg}')`
        : "";

      wrapper.style.backgroundImage = hasCustomBg
        ? `url('assets/backgrounds/${adat.background_style}')`
        : "";

      wrapper.classList.toggle("firev2-active", isFireV2);

      renderBadges(adat.badges);
      renderMatchHistory(adat.matches);

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

      const hasSecretBadge = adat.badges.some((b) => b.file === SECRET_BADGE_FILE);
      renderSecretBackgroundButton(hasSecretBadge);
      setProfileAccessState();
    });
}

function renderSecretBackgroundButton(hasSecretBadge) {
  if (!bgOptions) return;

  const existing = document.getElementById("secretEffectBtn");
  if (hasSecretBadge && isOwnProfile) {
    if (!existing) {
      const btn = document.createElement("button");
      btn.className = "bg-btn";
      btn.id = "secretEffectBtn";
      btn.dataset.bg = SECRET_BACKGROUND_STYLE;
      btn.textContent = "Fire V2";
      bgOptions.appendChild(btn);
    }
  } else {
    existing?.remove();
  }
}

function renderBadges(badges) {
  badgeLista.innerHTML = "";
  badges.forEach((b) => {
    const img = document.createElement("img");
    img.src = `assets/badges/${b.file}`;
    img.classList.add("badge");
    img.classList.add(b.rarity);
    img.title = `${b.name}\n${b.description}`;
    badgeLista.appendChild(img);
  });
}

function renderMatchHistory(matches) {
  meccsTabla.innerHTML = "";
  matches.forEach((m) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${m.game_id}</td>
      <td><a href="profile.html?user=${m.white}">${m.white}</a></td>
      <td><a href="profile.html?user=${m.black}">${m.black}</a></td>
      <td>${m.winner}</td>
    `;
    meccsTabla.appendChild(tr);
  });
}

function setProfileAccessState() {
  const pfpUpload = document.querySelector("#pfpUpload");
  const headerBgUpload = document.querySelector("#headerBgUpload");

  if (!isOwnProfile) {
    if (pfpUpload) pfpUpload.style.display = "none";
    if (headerBgUpload) headerBgUpload.style.display = "none";
    if (bgOptions) bgOptions.classList.add("disabled");
  } else {
    if (pfpUpload) pfpUpload.style.display = "block";
    if (headerBgUpload) headerBgUpload.style.display = "block";
    if (bgOptions) bgOptions.classList.remove("disabled");
  }
}

betoltProfil()
  .then(() => setProfileAccessState())
  .catch((err) => console.error("Profilload hiba:", err));

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

if (bgOptions) {
  bgOptions.addEventListener("click", (event) => {
    const btn = event.target;
    if (!btn.classList || !btn.classList.contains("bg-btn")) return;
    if (!isOwnProfile) return;

    const style = btn.dataset.bg;
    const form = new FormData();
    form.append("user", profilUser);
    form.append("style", style);

    fetch("../backend/save_background_style.php", {
      method: "POST",
      body: form,
    })
      .then((r) => r.json())
      .then(() => betoltProfil());
  });
}

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
