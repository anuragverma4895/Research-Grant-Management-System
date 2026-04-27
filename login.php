<?php
session_start();
require_once "config.php";
require_once "functions.php";
require_once "db_connection.php";

// Already logged in
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin'
        ? "admin_dashboard.php"
        : ($_SESSION['role'] === 'reviewer' ? "reviewer_dashboard.php" : "user_dashboard.php")));
    exit();
}

$error_message = "";
$user_not_found = false;
$login_role = isset($_GET['role']) && $_GET['role'] === 'reviewer' ? 'reviewer' : 'user';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $login_role = $_POST['login_role'] ?? 'user';

    if (!$username || !$password) {
        $error_message = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role, researcher_id
             FROM Users
             WHERE username = ? AND role = ?
             LIMIT 1"
        );

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("ss", $username, $login_role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['researcher_id'] = $user['researcher_id'];

                // Update last login
                $conn->query("UPDATE Users SET last_login = NOW() WHERE user_id = {$user['user_id']}");

                if ($user['role'] === 'reviewer') {
                    header("Location: reviewer_dashboard.php");
                } else {
                    header("Location: user_dashboard.php");
                }
                exit();
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $user_not_found = true;
            $error_message = "Account not found! Please create an account first.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo renderHead('Login'); ?>
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 30%, #312e81 60%, #4c1d95 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .tab-active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
        }

        .tab-inactive {
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
        }
    </style>
</head>

<body class="font-inter hero-gradient min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 pointer-events-none">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-primary-600/10 rounded-full blur-[120px]">
        </div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-accent-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md fade-in-up">
        <!-- Back to Home -->
        <a href="index.php"
            class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>

        <div class="glass-card rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="p-8 pb-6 text-center">
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-bold mb-1">Welcome Back</h1>
                <p class="text-gray-400 text-sm">Sign in to continue to your portal</p>
            </div>

            <!-- Role Tabs -->
            <div class="px-8 mb-6">
                <div class="flex bg-white/5 rounded-xl p-1 gap-1">
                    <button onclick="switchRole('user')" id="tab-user"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 <?php echo $login_role === 'user' ? 'tab-active' : 'tab-inactive hover:bg-white/10'; ?>">
                        Researcher
                    </button>
                    <button onclick="switchRole('reviewer')" id="tab-reviewer"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 <?php echo $login_role === 'reviewer' ? 'tab-active' : 'tab-inactive hover:bg-white/10'; ?>">
                        Reviewer
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            <div class="px-8">
                <?php if ($user_not_found): ?>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div>
                                <p class="text-red-400 font-medium text-sm">Account Not Found</p>
                                <a href="signup.php"
                                    class="text-red-300 text-xs underline hover:text-white mt-1 inline-block">Create a new
                                    account →</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message) && !$user_not_found): ?>
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
                        <p class="text-amber-400 text-sm font-medium"><?php echo clean($error_message); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Login Form -->
            <form method="POST" class="px-8 pb-8">
                <input type="hidden" name="login_role" id="login_role" value="<?php echo $login_role; ?>">

                <div class="mb-5">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" required id="login-username"
                            class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                            placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" required id="login-password"
                            class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                            placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" name="login" id="login-btn"
                    class="w-full py-3.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary-500/25 transition-all duration-300 text-sm">
                    Sign In
                </button>

                <div class="mt-6 text-center">
                    <p class="text-gray-500 text-sm">
                        Don't have an account?
                        <a href="signup.php" class="text-primary-400 hover:text-primary-300 font-semibold">Create
                            Account</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchRole(role) {
            document.getElementById('login_role').value = role;
            const userTab = document.getElementById('tab-user');
            const reviewerTab = document.getElementById('tab-reviewer');

            if (role === 'user') {
                userTab.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-active';
                reviewerTab.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-inactive hover:bg-white/10';
            } else {
                reviewerTab.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-active';
                userTab.className = 'flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-inactive hover:bg-white/10';
            }
        }
    </script>
    <script src="script.js"></script>
</body>

</html>