<?php
/**
 * ============================================
 * RESEARCH GRANT MANAGEMENT SYSTEM
 * Centralized Configuration File
 * ============================================
 */

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session Configuration
ini_set('session.save_path', '/tmp');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40850986');
define('DB_PASS', 'v5vVbqGRP7');
define('DB_NAME', 'if0_40850986_grant_db');

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'Research Grant Management System');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'https://grant-management-system.infinityfreeapp.com');

// Admin Password (hashed for security)
define('ADMIN_PASSWORD', 'Anurag@1123');

// API Key for REST endpoints
define('API_KEY', 'rgms_api_2025_secure_key');

// ============================================
// FILE UPLOAD SETTINGS
// ============================================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['application/pdf']);
define('ALLOWED_EXTENSIONS', ['pdf']);

// ============================================
// EMAIL SETTINGS (PHPMailer)
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', ''); // Add your Gmail
define('SMTP_PASS', ''); // Add App Password
define('SMTP_FROM_NAME', APP_NAME);

// ============================================
// ROLES
// ============================================
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
define('ROLE_REVIEWER', 'reviewer');

// ============================================
// STATUS VALUES
// ============================================
define('STATUS_SUBMITTED', 'Submitted');
define('STATUS_UNDER_REVIEW', 'Under Review');
define('STATUS_APPROVED', 'Approved');
define('STATUS_REJECTED', 'Rejected');
define('STATUS_ON_HOLD', 'On Hold');

define('ALLOWED_STATUSES', [
    STATUS_SUBMITTED,
    STATUS_UNDER_REVIEW,
    STATUS_APPROVED,
    STATUS_REJECTED,
    STATUS_ON_HOLD
]);
?>
