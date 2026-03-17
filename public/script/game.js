const tablaElem = document.querySelector("#tabla");
const infoElem = document.querySelector("#info");
const jatekosElem = document.querySelector("#jatekosInfo");
const statusElem = document.querySelector("#statusInfo");

let gameId = null;
let jatekos = null;
let jatekosSzin = null;
let kijelolt = null;
let lastMove = null;

const params = new URLSearchParams(window.location.search);
gameId = params.get("game");

jatekos = localStorage.getItem("felhasznalo");

if (!gameId) {
  alert("Nincs game ID.");
  window.location.href = "game.html";
}

if (!jatekos) {
  alert("Előbb jelentkezz be.");
  window.location.href = "index.html";
}

frissites();
setInterval(frissites, 1000);

function frissites() {
  fetch(`../backend/game_state.php?game=${gameId}`)
    .then(r => r.json())
    .then(adat => {
      if (adat.status !== "ok") return;

      const tabla = adat.board;
      const white = adat.white;
      const black = adat.black;
      const turn = adat.turn;
      const status = adat.status_game;

      if (!jatekosSzin) {
        if (jatekos === white) jatekosSzin = "white";
        if (jatekos === black) jatekosSzin = "black";
      }

      jatekosElem.textContent = `Te: ${jatekos} (${jatekosSzin})`;
      infoElem.textContent = `Soron: ${turn}`;
      statusElem.textContent = `Státusz: ${status}`;

      kirajzolTabla(tabla);

      if (status !== "ongoing") {
        alert("A játék véget ért.");
        window.location.href = window.location.origin + "/sakk-js/public";
      }
    });
}

function kirajzolTabla(tabla) {
  tablaElem.innerHTML = "";
  document.querySelectorAll(".mezo").forEach(m => m.classList.remove("lephet"));

  let megjelenitettTabla = tabla;

  if (jatekosSzin === "black") {
    megjelenitettTabla = [...tabla].reverse().map(s => [...s].reverse());
  }

  for (let sor = 0; sor < 8; sor++) {
    for (let oszlop = 0; oszlop < 8; oszlop++) {
      const mezo = document.createElement("div");
      mezo.className = "mezo";

      if ((sor + oszlop) % 2 === 0) {
        mezo.style.backgroundColor = "#f0d9b5";
      } else {
        mezo.style.backgroundColor = "#b58863";
      }

      const figura = megjelenitettTabla[sor][oszlop];
      mezo.innerHTML = figuraHTML(figura);
      mezo.dataset.figura = figura;

      mezo.dataset.sor = sor;
      mezo.dataset.oszlop = oszlop;

      mezo.addEventListener("click", () => mezoKatt(sor, oszlop));

      if (kijelolt) {
        if (kijelolt.sor === sor && kijelolt.oszlop === oszlop) {
          mezo.classList.add("kijelolt");
        }
      }

      tablaElem.appendChild(mezo);
    }
  }

  if (lastMove) {
    const idx = lastMove.toRow * 8 + lastMove.toCol;
    const mezo = tablaElem.children[idx];

    if (mezo) {
      mezo.classList.add("lepes-anim");
      mezo.classList.add("por-effekt");

      setTimeout(() => {
        mezo.classList.remove("lepes-anim");
        mezo.classList.remove("por-effekt");
      }, 400);
    }

    lastMove = null;
  }
}

function figuraJel(f) {
  switch (f) {
    case "P": return "♙";
    case "R": return "♖";
    case "N": return "♘";
    case "B": return "♗";
    case "Q": return "♕";
    case "K": return "♔";
    case "p": return "♟";
    case "r": return "♜";
    case "n": return "♞";
    case "b": return "♝";
    case "q": return "♛";
    case "k": return "♚";
    default: return "";
  }
}

function figuraHTML(f) {
  if (!f) return "";
  const isWhite = f === f.toUpperCase();
  const jel = figuraJel(f);
  return `<span class="${isWhite ? "white-piece" : "black-piece"}">${jel}</span>`;
}

function mezoKatt(sor, oszlop) {
  if (!kijelolt) {
    kijelolt = { sor, oszlop };

    document.querySelectorAll(".mezo").forEach(m => m.classList.remove("lephet"));

    const lephetLista = szamoljLepeseket(sor, oszlop);

    lephetLista.forEach(pos => {
      const idx = pos.sor * 8 + pos.oszlop;
      const mezo = tablaElem.children[idx];
      if (mezo) mezo.classList.add("lephet");
    });

    return;
  }

  let fromRow = kijelolt.sor;
  let fromCol = kijelolt.oszlop;

  let toRow = sor;
  let toCol = oszlop;

  if (jatekosSzin === "black") {
    fromRow = 7 - fromRow;
    fromCol = 7 - fromCol;
    toRow = 7 - toRow;
    toCol = 7 - toCol;
  }

  lastMove = { toRow: sor, toCol: oszlop };

  kijelolt = null;

  document.querySelectorAll(".mezo").forEach(m => m.classList.remove("lephet"));

  kuldLepes(fromRow, fromCol, toRow, toCol);
}

function kuldLepes(fromRow, fromCol, toRow, toCol) {
  const adat = new FormData();
  adat.append("game", gameId);
  adat.append("user", jatekos);
  adat.append("fromRow", fromRow);
  adat.append("fromCol", fromCol);
  adat.append("toRow", toRow);
  adat.append("toCol", toCol);

  fetch("../backend/move.php", {
    method: "POST",
    body: adat
  })
    .then(r => r.json())
    .then(valasz => {
      if (valasz.status !== "ok") {
        alert("Hiba: " + valasz.msg);
      }
    });
}

function szamoljLepeseket(sor, oszlop) {
  const tabla = Array.from(tablaElem.children).map(m => m.dataset.figura || "");
  const figura = tabla[sor * 8 + oszlop];
  if (!figura) return [];

  const isWhite = figura === figura.toUpperCase();
  const lephet = [];

  function addIfValid(r, c) {
    if (r < 0 || r > 7 || c < 0 || c > 7) return;
    lephet.push({ sor: r, oszlop: c });
  }

  function isEmpty(r, c) {
    return tabla[r * 8 + c] === "";
  }

  const f = figura.toUpperCase();

  if (f === "P") {
    if (isWhite) {
      if (isEmpty(sor - 1, oszlop)) {
        addIfValid(sor - 1, oszlop);
        if (sor === 6 && isEmpty(sor - 2, oszlop)) addIfValid(sor - 2, oszlop);
      }
    } else {
      if (isEmpty(sor + 1, oszlop)) {
        addIfValid(sor + 1, oszlop);
        if (sor === 1 && isEmpty(sor + 2, oszlop)) addIfValid(sor + 2, oszlop);
      }
    }
  }

  if (f === "N") {
    const L = [
      [1, 2], [2, 1], [-1, 2], [-2, 1],
      [1, -2], [2, -1], [-1, -2], [-2, -1]
    ];
    L.forEach(([dr, dc]) => addIfValid(sor + dr, oszlop + dc));
  }

  if (f === "B" || f === "Q") {
    for (let d of [[1,1],[1,-1],[-1,1],[-1,-1]]) {
      for (let i = 1; i < 8; i++) addIfValid(sor + d[0] * i, oszlop + d[1] * i);
    }
  }

  if (f === "R" || f === "Q") {
    for (let d of [[1,0],[-1,0],[0,1],[0,-1]]) {
      for (let i = 1; i < 8; i++) addIfValid(sor + d[0] * i, oszlop + d[1] * i);
    }
  }

  if (f === "K") {
    for (let dr of [-1,0,1]) {
      for (let dc of [-1,0,1]) {
        if (dr !== 0 || dc !== 0) addIfValid(sor + dr, oszlop + dc);
      }
    }
  }

  return lephet;
}
