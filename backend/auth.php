<?php
session_start();

$public = [
    "login.php",
    "register.php",
    "logout.php",
    "whoami.php",
    "join_matchmaking.php",
    "poll_matchmaking.php",
    "profile.php",
    "game_state.php",
    "move.php",
    "leaderboard.php",
    "finish_game.php",
    "save_background_style.php",
    "upload_header.php",
    "upload_pfp.php"
];

$adminOnly = [
    "admin_badge.php"
];

$current = basename($_SERVER["PHP_SELF"]);

if (in_array($current, $public)) {
    return;
}

if (in_array($current, $adminOnly)) {
    if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "boronen") {
        header("Location: profile.html");
        exit;
    }
    return;
}

if (!isset($_SESSION["user"])) {
    echo json_encode([
        "status" => "error",
        "message" => "forbidden"
    ]);
    exit;
}
