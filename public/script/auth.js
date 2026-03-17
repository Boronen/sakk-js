const felhasznaloInput = document.querySelector("#felhasznaloInput");
const jelszoInput = document.querySelector("#jelszoInput");
const belepesGomb = document.querySelector("#belepesGomb");
const regisztracioGomb = document.querySelector("#regisztracioGomb");
const authUzenet = document.querySelector("#authUzenet");
const authBlokk = document.querySelector("#authBlokk");
const udvozloBlokk = document.querySelector("#udvozloBlokk");
const felhasznaloNevSzoveg = document.querySelector("#felhasznaloNevSzoveg");
const kijelentkezesGomb = document.querySelector("#kijelentkezesGomb");

if (belepesGomb) belepesGomb.addEventListener("click", belepes);
if (regisztracioGomb) regisztracioGomb.addEventListener("click", regisztracio);
if (kijelentkezesGomb) kijelentkezesGomb.addEventListener("click", kijelentkezes);

initAuth();


function initAuth() {
    const mentettFelhasznalo = localStorage.getItem("felhasznalo");
    if (mentettFelhasznalo) {
        bejelentkezveUI(mentettFelhasznalo);
    } else {
        kijelentkezveUI();
    }
}

function regisztracio() {
    const felhasznalo = felhasznaloInput.value.trim();
    const jelszo = jelszoInput.value.trim();

    if (!felhasznalo || !jelszo) {
        authUzenet.textContent = "Töltsd ki mindkét mezőt.";
        return;
    }

    const adat = new FormData();
    adat.append("user", felhasznalo);
    adat.append("pass", jelszo);

    fetch("../backend/register.php", {
        method: "POST",
        body: adat
    })
        .then(r => r.text())
        .then(valasz => {
            if (valasz === "ok") {
                authUzenet.textContent = "Sikeres regisztráció, most jelentkezz be.";
            } else if (valasz === "exists") {
                authUzenet.textContent = "Már létezik ilyen felhasználó.";
            } else {
                authUzenet.textContent = "Hiba: " + valasz;
            }
        })
        .catch(() => {
            authUzenet.textContent = "Hálózati hiba.";
        });
}

function belepes() {
    const felhasznalo = felhasznaloInput.value.trim();
    const jelszo = jelszoInput.value.trim();

    if (!felhasznalo || !jelszo) {
        authUzenet.textContent = "Töltsd ki mindkét mezőt.";
        return;
    }

    const adat = new FormData();
    adat.append("user", felhasznalo);
    adat.append("pass", jelszo);

    fetch("../backend/login.php", {
        method: "POST",
        body: adat
    })
        .then(r => r.text())
        .then(valasz => {
            if (valasz === "ok") {
                localStorage.setItem("felhasznalo", felhasznalo);
                bejelentkezveUI(felhasznalo);
            } else if (valasz === "no_user") {
                authUzenet.textContent = "Nincs ilyen felhasználó.";
            } else if (valasz === "wrong_pass") {
                authUzenet.textContent = "Hibás jelszó.";
            } else {
                authUzenet.textContent = "Hiba: " + valasz;
            }
        })
        .catch(() => {
            authUzenet.textContent = "Hálózati hiba.";
        });
}

function kijelentkezes() {
    localStorage.removeItem("felhasznalo");
    kijelentkezveUI();
}

function bejelentkezveUI(felhasznalo) {
    if (felhasznaloNevSzoveg) {
        felhasznaloNevSzoveg.textContent = felhasznalo;
    }
    if (authBlokk) authBlokk.style.display = "none";
    if (udvozloBlokk) udvozloBlokk.style.display = "block";

    if (kijelentkezesGomb) kijelentkezesGomb.style.display = "inline-block";

    if (authUzenet) authUzenet.textContent = "";
}

function kijelentkezveUI() {
    if (authBlokk) authBlokk.style.display = "block";
    if (udvozloBlokk) udvozloBlokk.style.display = "none";

    if (kijelentkezesGomb) kijelentkezesGomb.style.display = "none";

    if (authUzenet) authUzenet.textContent = "";
}
