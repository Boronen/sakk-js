<?php
session_start();
echo isset($_SESSION["user"]) ? $_SESSION["user"] : "no-session";
