<?php
/**
 * ============================================
 * AUTHENTICATION & AUTHORIZATION SYSTEM
 * Supports: Admin, User (Researcher), Reviewer
 * ============================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

/* ============================
   ROLE CHECK HELPERS
============================ */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_USER;
}

function isReviewer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_REVIEWER;
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
        if (isAdmin()) {
            header("Location: admin_dashboard.php");
        } elseif (isReviewer()) {
            header("Location: reviewer_dashboard.php");
        } else {
            header("Location: login.php");
        }
        exit();
    }
}

function requireReviewer() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    if (!isReviewer()) {
        if (isAdmin()) {
            header("Location: admin_dashboard.php");
        } elseif (isUser()) {
            header("Location: user_dashboard.php");
        } else {
            header("Location: login.php");
        }
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
        'reviewer_id' => $_SESSION['reviewer_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null
    ];
}

/**
 * Get the dashboard URL for the current user's role
 */
function getDashboardURL() {
    if (isAdmin()) return 'admin_dashboard.php';
    if (isReviewer()) return 'reviewer_dashboard.php';
    return 'user_dashboard.php';
}
?>
