<?php
/**
 * ============================================
 * REUSABLE HELPER FUNCTIONS
 * ============================================
 */

/**
 * Sanitize user input
 */
function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for database (trim only, escaping done by prepared statements)
 */
function sanitize($data) {
    return trim($data ?? '');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format currency in INR
 */
function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

/**
 * Format date to readable format
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Format date with time
 */
function formatDateTime($date) {
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Get status badge CSS class
 */
function getStatusClass($status) {
    $classes = [
        'Submitted'    => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'Under Review'  => 'bg-blue-100 text-blue-800 border-blue-300',
        'Approved'      => 'bg-green-100 text-green-800 border-green-300',
        'Rejected'      => 'bg-red-100 text-red-800 border-red-300',
        'On Hold'       => 'bg-gray-100 text-gray-800 border-gray-300'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Get status icon
 */
function getStatusIcon($status) {
    $icons = [
        'Submitted'    => '📤',
        'Under Review'  => '🔍',
        'Approved'      => '✅',
        'Rejected'      => '❌',
        'On Hold'       => '⏸️'
    ];
    return $icons[$status] ?? '📋';
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message system
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Handle file upload
 */
function handleFileUpload($file, $prefix = 'doc') {
    // Check if upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Only PDF files are allowed.'];
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type.'];
    }

    // Generate unique filename
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => 'uploads/' . $filename];
    }

    return ['success' => false, 'error' => 'Failed to save file.'];
}

/**
 * Create a notification
 */
function createNotification($conn, $user_id, $message, $type = 'System') {
    $stmt = $conn->prepare("INSERT INTO Notifications (user_id, message, notification_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $message, $type);
    return $stmt->execute();
}

/**
 * Render the common Tailwind head section
 */
function renderHead($title = 'RGMS') {
    $appName = APP_NAME;
    return <<<HTML
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{$appName} - Empowering Research, Enabling Innovation">
    <title>{$title} - {$appName}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
                        accent: { 50:'#fdf4ff',100:'#fae8ff',200:'#f5d0fe',300:'#f0abfc',400:'#e879f9',500:'#d946ef',600:'#c026d3',700:'#a21caf',800:'#86198f',900:'#701a75' },
                        dark: { 700:'#1e293b',800:'#0f172a',900:'#020617' },
                    }
                }
            }
        }
    </script>
HTML;
}

/**
 * Render sidebar navigation for dashboards
 */
function renderSidebar($role, $activePage = '') {
    $menuItems = [];
    
    if ($role === ROLE_ADMIN) {
        $menuItems = [
            ['url' => 'admin_dashboard.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'label' => 'Dashboard', 'key' => 'dashboard'],
            ['url' => 'manage_applications.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'label' => 'Applications', 'key' => 'applications'],
            ['url' => 'manage_researchers.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'label' => 'Researchers', 'key' => 'researchers'],
            ['url' => 'manage_agencies.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', 'label' => 'Agencies', 'key' => 'agencies'],
            ['url' => 'grant_valuation_panel.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', 'label' => 'Valuation Panel', 'key' => 'valuation'],
        ];
    } elseif ($role === ROLE_USER) {
        $menuItems = [
            ['url' => 'user_dashboard.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'label' => 'Dashboard', 'key' => 'dashboard'],
            ['url' => 'apply_grant.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'label' => 'Apply for Grant', 'key' => 'apply'],
            ['url' => 'my_applications.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'label' => 'My Applications', 'key' => 'applications'],
            ['url' => 'grant_application.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>', 'label' => 'Quick Apply', 'key' => 'quick_apply'],
        ];
    } elseif ($role === ROLE_REVIEWER) {
        $menuItems = [
            ['url' => 'reviewer_dashboard.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'label' => 'Dashboard', 'key' => 'dashboard'],
            ['url' => 'review_applications.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', 'label' => 'Review Applications', 'key' => 'review'],
        ];
    }
    
    $username = clean($_SESSION['username'] ?? 'User');
    $roleLabel = ucfirst($role);
    $roleColors = [
        'admin' => 'from-red-500 to-rose-600',
        'user' => 'from-primary-500 to-accent-600',
        'reviewer' => 'from-emerald-500 to-teal-600',
    ];
    $gradient = $roleColors[$role] ?? 'from-primary-500 to-accent-600';
    
    $html = <<<HTML
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-800 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo Section -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {$gradient} flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-sm leading-tight">RGMS</h1>
                <p class="text-gray-400 text-xs">{$roleLabel} Panel</p>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="px-4 py-4 space-y-1 flex-1 overflow-y-auto">
HTML;

    foreach ($menuItems as $item) {
        $isActive = ($activePage === $item['key']);
        $activeClass = $isActive 
            ? 'bg-gradient-to-r ' . $gradient . ' text-white shadow-lg' 
            : 'text-gray-400 hover:bg-white/5 hover:text-white';
        
        $html .= <<<HTML
            <a href="{$item['url']}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {$activeClass}">
                {$item['icon']}
                <span class="font-medium text-sm">{$item['label']}</span>
            </a>
HTML;
    }

    $html .= <<<HTML
        </nav>
        
        <!-- User Section -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br {$gradient} flex items-center justify-center">
                    <span class="text-white font-bold text-sm">{$username[0]}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{$username}</p>
                    <p class="text-gray-400 text-xs">{$roleLabel}</p>
                </div>
                <a href="logout.php" class="text-gray-400 hover:text-red-400 transition-colors" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>
    
    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>
    
    <!-- Mobile Toggle Button -->
    <button onclick="toggleSidebar()" class="fixed top-4 left-4 z-50 lg:hidden bg-dark-800 text-white p-2 rounded-xl shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
HTML;
    
    return $html;
}
?>
