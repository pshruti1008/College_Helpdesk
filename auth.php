<?php


function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("location: login.php");
        exit;
    }
}

function getUserRole() {
    return $_SESSION["role"] ?? null;
}

function canAccessPage($allowedRoles) {
    $userRole = getUserRole();
    return in_array($userRole, $allowedRoles);
}
?>