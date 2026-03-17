<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "boronen") {
    header("Location: profile.html");
    exit;
}

$badgeDir = __DIR__ . "/assets/badges/";
$badges = array_values(array_filter(scandir($badgeDir), function($f) {
    return !in_array($f, ['.', '..']);
}));

$badgeMetaFile = __DIR__ . "/../backend/data/badges.json";
$badgeMeta = file_exists($badgeMetaFile)
    ? json_decode(file_get_contents($badgeMetaFile), true)
    : [];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Badge Admin Panel</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .panel { max-width: 400px; padding: 20px; border: 2px solid black; border-radius: 8px; margin-bottom: 30px; }
        input, select, button { width: 100%; padding: 10px; margin-top: 10px; }
        #status, #uploadStatus { margin-top: 15px; font-weight: bold; }
        .badge-list { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .badge-item { text-align: center; font-size: 12px; width: 90px; }
        .badge-item img { width: 64px; height: 64px; border-radius: 4px; display: block; margin: 0 auto 4px; }
        .common { filter: drop-shadow(0 0 4px gray); }
        .rare { filter: drop-shadow(0 0 4px #3b82f6); }
        .epic { filter: drop-shadow(0 0 6px #a855f7); }
        .legendary { filter: drop-shadow(0 0 8px #facc15); }
    </style>
</head>
<body>

<h2>Badge Admin Panel</h2>

<h3>Jelenlegi badge-ek</h3>
<div class="badge-list">
    <?php foreach ($badges as $b):
        $meta = $badgeMeta[$b] ?? null;
        $rarity = $meta["rarity"] ?? "common";
        $name = $meta["name"] ?? $b;
        $desc = $meta["description"] ?? "";
    ?>
        <div class="badge-item">
            <img src="assets/badges/<?php echo htmlspecialchars($b); ?>"
                 class="<?php echo htmlspecialchars($rarity); ?>"
                 title="<?php echo htmlspecialchars($name . "\n" . $desc); ?>">
            <div><?php echo htmlspecialchars($b); ?></div>
            <div><?php echo htmlspecialchars($rarity); ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel">
    <h3>Új badge feltöltése</h3>

    <label>Badge fájlnév (pl. win_10.png)</label>
    <input type="text" id="newBadgeName">

    <label>Rarity</label>
    <select id="newBadgeRarity">
        <option value="common">common</option>
        <option value="rare">rare</option>
        <option value="epic">epic</option>
        <option value="legendary">legendary</option>
    </select>

    <label>Leírás</label>
    <input type="text" id="newBadgeDesc">

    <label>Badge kép</label>
    <input type="file" id="newBadgeFile">

    <button id="uploadBtn">Badge feltöltése</button>

    <div id="uploadStatus"></div>
</div>

<div class="panel">
    <h3>Badge kiosztása</h3>

    <label>Felhasználónév</label>
    <input type="text" id="user">

    <label>Badge kiválasztása</label>
    <select id="badge">
        <?php foreach ($badges as $b): ?>
            <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
        <?php endforeach; ?>
    </select>

    <button id="giveBtn">Badge kiosztása</button>

    <div id="status"></div>
</div>

<script>
document.getElementById("giveBtn").addEventListener("click", () => {
    const user = document.getElementById("user").value.trim();
    const badge = document.getElementById("badge").value;

    if (!user) {
        document.getElementById("status").textContent = "Írd be a felhasználó nevét.";
        return;
    }

    const form = new FormData();
    form.append("user", user);
    form.append("badge", badge);

    fetch("../backend/give_badge.php", {
        method: "POST",
        body: form
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById("status").textContent =
            res.status === "ok" ? "Badge kiosztva." : "Hiba: " + res.message;
    })
    .catch(() => {
        document.getElementById("status").textContent = "Hálózati hiba.";
    });
});

document.getElementById("uploadBtn").addEventListener("click", () => {
    const name = document.getElementById("newBadgeName").value.trim();
    const rarity = document.getElementById("newBadgeRarity").value;
    const desc = document.getElementById("newBadgeDesc").value.trim();
    const file = document.getElementById("newBadgeFile").files[0];

    if (!name || !file) {
        document.getElementById("uploadStatus").textContent = "Adj meg nevet és képet.";
        return;
    }

    const form = new FormData();
    form.append("name", name);
    form.append("rarity", rarity);
    form.append("description", desc);
    form.append("file", file);

    fetch("../backend/upload_badge.php", {
        method: "POST",
        body: form
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById("uploadStatus").textContent =
            res.status === "ok" ? "Badge feltöltve." : "Hiba: " + res.message;

        if (res.status === "ok") location.reload();
    })
    .catch(() => {
        document.getElementById("uploadStatus").textContent = "Hálózati hiba.";
    });
});
</script>

</body>
</html>
