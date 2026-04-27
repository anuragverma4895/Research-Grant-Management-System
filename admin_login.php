<?php
session_start();
require_once "config.php";
require_once "functions.php";

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['admin_login'])) {
    $password = $_POST['password'];

    if (empty($password)) {
        $error_message = "Please enter the admin password.";
    } else {
        if ($password === ADMIN_PASSWORD) {
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = "Admin";
            $_SESSION['role'] = "admin";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid admin password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Admin Login'); ?>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1a0a0a 30%, #3b0764 60%, #4c1d95 100%); }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
    </style>
</head>
<body class="font-inter hero-gradient min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-red-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-rose-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md fade-in-up">
        <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Home
        </a>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-8 pb-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-500/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-bold mb-1">Admin Access</h1>
                <p class="text-gray-400 text-sm">Authorized Personnel Only</p>
            </div>

            <div class="px-8">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                        <p class="text-red-400 text-sm font-medium"><?php echo clean($error_message); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" class="px-8 pb-8">
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Admin Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <input type="password" name="password" required id="admin-password"
                            class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none transition-all text-sm"
                            placeholder="Enter admin password">
                    </div>
                </div>

                <button type="submit" name="admin_login" id="admin-login-btn"
                    class="w-full py-3.5 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-red-500/25 transition-all duration-300 text-sm">
                    Access Admin Panel
                </button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
