<?php
header("Content-Type: application/json; charset=utf-8");

$usersFile = __DIR__ . "/data/users.json";
$gamesFile = __DIR__ . "/data/games.json";
$badgesMetaFile = __DIR__ . "/data/badges.json";

$userParam = $_GET["user"] ?? "";
$userParam = strtolower(trim($userParam));

if ($userParam === "") {
    echo json_encode(["status" => "error", "message" => "missing_user"]);
    exit;
}

if (!file_exists($usersFile)) {
    echo json_encode(["status" => "error", "message" => "no_users"]);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);
if (!isset($users[$userParam])) {
    echo json_encode(["status" => "error", "message" => "user_not_found"]);
    exit;
}

$badgeMeta = file_exists($badgesMetaFile)
    ? json_decode(file_get_contents($badgesMetaFile), true)
    : [];

$u = $users[$userParam];

$badges = [];
foreach ($u["badges"] ?? [] as $file) {
    $meta = $badgeMeta[$file] ?? null;
    $badges[] = [
        "file" => $file,
        "name" => $meta["name"] ?? $file,
        "rarity" => $meta["rarity"] ?? "common",
        "description" => $meta["description"] ?? ""
    ];
}

$matches = [];
if (file_exists($gamesFile)) {
    $games = json_decode(file_get_contents($gamesFile), true);
    foreach ($games as $id => $g) {
        if (($g["white"] ?? "") === $userParam || ($g["black"] ?? "") === $userParam) {
            $matches[] = [
                "game_id" => $id,
                "white" => $g["white"],
                "black" => $g["black"],
                "winner" => $g["winner"] ?? "-"
            ];
        }
    }
}

$win = $u["win"] ?? 0;
$lose = $u["lose"] ?? 0;
$elo = $u["elo"] ?? 1200;
$winrate = ($win + $lose > 0) ? round($win / ($win + $lose) * 100, 1) : 0;

echo json_encode([
    "status" => "ok",
    "user" => $userParam,
    "elo" => $elo,
    "win" => $win,
    "lose" => $lose,
    "winrate" => $winrate,
    "pfp" => $u["pfp"] ?? "default_pfp.png",
    "header_bg" => $u["header_bg"] ?? null,
    "background_style" => $u["background_style"] ?? "default",
    "badges" => $badges,
    "matches" => $matches
], JSON_UNESCAPED_UNICODE);
