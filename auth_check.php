<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   BASIC AUTH HELPERS
============================ */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/* ============================
   AUTH GUARDS
============================ */
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

function requireUser() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    if (!isUser()) {
        header("Location: admin_dashboard.php");
        exit();
    }
}

/* ============================
   CURRENT USER DATA
============================ */
function getCurrentUser() {
    if (!isLoggedIn()) return null;

    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'researcher_id' => $_SESSION['researcher_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null
    ];
}
?>
