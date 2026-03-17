# sakk-matt  
### avagy: „egy sakkjáték, ami valahogy működik, aztán majd egyszer rendesen is megírom”

Ez itt a **sakk-matt**, egy webes sakkjáték, amit teljesen egyedül raktam össze, mert miért ne.  
Na jó… *majdnem* egyedül.  
A Microsoft Copilotnak is jár egy köszönet, mert amikor épp agyfaszt kaptam, sokszor kihúzott a szarból.  
Máskor meg ő adott agyfaszt. Szóval kiegyenlített a kapcsolat.

Van benne minden, ami kell:

- meccsek  
- ELO  
- profiloldal  
- badge-ek (túl sok badge)  
- admin panel  
- JSON alapú „adatbázis”, mert utálom az SQL-t  
- és egy titkos ultra-rare badge, amit csak a kiválasztottak tudnak megszerezni

A projekt jelenleg kb. olyan állapotban van, mint egy félkész IKEA bútor:  
**működik, csak nem szabad túl erősen ránézni.**

---

## Funkciók

### Sakk
Igen, lehet sakkozni.  
Nem, nem fogom elmagyarázni hogyan.  
De azért egy minimális technikai háttér, hogy ne tűnjön teljes fekete mágiának:

- A lépések validálása teljesen saját implementáció (`game.js`).  
- A rendszer figyeli:
  - melyik bábu hova léphet,  
  - sakkban áll-e a király,  
  - matt/patt helyzetet,  
  - és hogy a játékos nem léphet-e öngyilkos módon.  
- A szerver oldalon a PHP csak a meccs állapotát tárolja JSON-ban (igen, JSON-ban, mert SQL-t nem akartam).  
- A játék logikája nem engine, hanem kézzel írt szabályrendszer — emiatt néha túl okos, néha túl hülye, de legalább az enyém.

### Profilrendszer
- profilkép  
- háttér  
- statok  
- meccstörténet  
- badge-ek (mert kell a dopamin)

### Badge rendszer
A badge-ek JSON-ban vannak, mert:
- gyors  
- egyszerű  
- és mert lusta voltam adatbázist csinálni

Van:
- common  
- rare  
- epic  
- legendary  
- **ultra-rare** (igen, ez külön tier, mert miért ne)

### Titkos badge
Ha a profilodon legalább **10 win** van, és megnyitod a konzolt, kapsz egy rejtvényt.  
Ha megoldod → kapsz egy ultra-rare badge-et.  
Ha nem → hát, skill issue.

---

## Fájlstruktúra (avagy: mi micsoda, emberi nyelven)

### **backend/**
A szerveroldali PHP-k, amik úgy tesznek, mintha egy backend lennének.

- **auth.php** – bejelentkezés, regisztráció, jelszókezelés  
- **profile.php** – profiladatok összegyűjtése JSON-ból  
- **give_badge.php** – badge kiosztása (admin vagy titkos puzzle)  
- **matchmaking.php** – két játékost összehoz  
- **game_state.php** – meccs állapotának mentése/betöltése  
- **upload_pfp.php** – profilkép feltöltése  
- **upload_header.php** – fejléc háttér feltöltése  
- **save_background_style.php** – profil háttér beállítása  
- **data/** – az egész „adatbázis” (JSON fájlok, amikért majd egyszer biztos lesz SQL… vagy nem)

### **public/**
A frontend, amit a böngésző lát.

- **index.html** – kezdőoldal  
- **profile.html** – profiloldal  
- **game.html** – maga a sakk  
- **assets/** – képek, badge-ek, hátterek, pfp-k  
- **script/** – JavaScript fájlok:
  - **auth.js** – bejelentkezés/regisztráció logika  
  - **profile.js** – profil betöltése, badge-ek, titkos puzzle  
  - **game.js** – a sakk agya (ha agynak lehet nevezni)  
  - **leaderboard.js** – ranglista  
  - **matchmaking.js** – játékoskeresés  
  - **flappy.js** – igen, van benne flappy is, ne kérdezd miért

---

## Telepítés (ha valaki más is futtatná… bár kétlem)

1. Tedd be egy webszerver alá (XAMPP, nginx, Apache, bármi).
2. A `public/` mappát szolgáld ki.
3. A `backend/` mappát ne szolgáld ki (nyilván).
4. Kész.

Ha valami nem működik, akkor:
- vagy rossz helyre tetted  
- vagy a böngésződ utál  
- vagy én rontottam el valamit  
- vagy mindhárom egyszerre

---

## JSON „adatbázis”
Igen, tudom, hogy ez nem skálázódik.  
Igen, tudom, hogy SQL jobb lenne.  
Nem, nem fogom átírni.  
Majd egyszer. Talán. Valamikor. Vagy nem.

---

## Admin panel
Van.  
Csak én érem el.  
És badge-eket tudok spammelni vele, ami a legfontosabb feature.

---

## Kódminőség
Ne nézd meg közelről.  
Komolyan.  
Ez a projekt olyan, mint egy festmény:  
**távolról szép.**

---

## Licenc
Nincs.  
Használd, ne használd, másold, töröld, csinálj vele amit akarsz.  
Ha valami elromlik, az a te hibád.

---

## Köszönet
Magamnak, mert valakinek csak el kellett viselnie ezt az egészet.  
Meg a koffeinnek, ami nélkül ez a projekt már az első nap után elvérzett volna.  

Illetve Hapcy-nak  
[https://no.linkedin.com/in/tibor-moger]  
a debugért, a *még több* debugért, és azért, hogy rávilágított: a `map`-et kurvára nem úgy kell használni, ahogy elsőre gondoltam.  
(Még mindig nem értem teljesen, szóval majd le kell csücsülnöm vele beszélni.)  

Meg annak is jár egy köszönet, hogy a JSON még nem robbant fel.  
És persze a Copilotnak: amikor épp agyfaszt kaptam, sokszor kihúzott a szarból.  
Máskor meg ő adott agyfaszt.  
Szóval korrekt a viszony.  
#ilovetoxicrelationship  

---

### Projekt idővonal
- **Kezdete:** 2026. 03. 09.  
- **„Befejezése”** (haha, jó vicc): 2026. 03. 17., hajnali 2:37  
- **Eltöltött órák:** NEM AKAROM TUDNI  
  *\*sírás hangok\**

---
## ui, 
- ### a cookiekat full idiótán kezeltem [főleg azért mert nem tudtam eleinte hogy működnek] , szóval BÁRMIT csinálsz ajánlom inkább, hogy privát böngészőben próbáld, mert ez a része **is** káoszos a kódnak