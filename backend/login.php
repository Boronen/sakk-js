<?php
header("Content-Type: text/plain; charset=utf-8");

$user = $_POST["user"] ?? "";
$pass = $_POST["pass"] ?? "";

$usersFile = __DIR__ . "/data/users.json";
$users = json_decode(file_get_contents($usersFile), true);

if (!isset($users[$user])) { echo "no_user"; exit; }
if (!password_verify($pass, $users[$user]["password"])) { echo "wrong_pass"; exit; }

$_SESSION["user"] = $user;

echo "ok";
