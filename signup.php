<?php
ini_set('session.save_path', '/tmp');
session_start();
require_once "config.php";
require_once "functions.php";
require_once "db_connection.php";

$success_message = "";
$error_message = "";
$account_created = false;
$created_username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $first_name = sanitize($_POST['first_name']);
    $last_name  = sanitize($_POST['last_name']);
    $email      = sanitize($_POST['email']);
    $username   = sanitize($_POST['username']);
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone        = sanitize($_POST['phone']);
    $institution  = sanitize($_POST['institution']);
    $department   = sanitize($_POST['department']);
    $research_area = sanitize($_POST['research_area']);
    $signup_role = sanitize($_POST['signup_role'] ?? 'user');

    // Validate role
    if (!in_array($signup_role, ['user', 'reviewer'])) {
        $signup_role = 'user';
    }

    if ($first_name === "" || $last_name === "" || $email === "" || $username === "" || $password === "") {
        $error_message = "Please fill all required fields.";
    } elseif (!isValidEmail($email)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters.";
    } else {
        $check_email = $conn->prepare("SELECT researcher_id FROM Researchers WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $res_email = $check_email->get_result();

        if ($res_email->num_rows > 0) {
            $error_message = "This email is already registered. Please login.";
        } else {
            $check_user = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
            $check_user->bind_param("s", $username);
            $check_user->execute();
            $res_user = $check_user->get_result();

            if ($res_user->num_rows > 0) {
                $error_message = "Username already taken. Choose another.";
            } else {
                $stmt_researcher = $conn->prepare("
                    INSERT INTO Researchers (first_name, last_name, email, phone, institution, department, research_area)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_researcher->bind_param("sssssss", $first_name, $last_name, $email, $phone, $institution, $department, $research_area);

                if ($stmt_researcher->execute()) {
                    $researcher_id = $stmt_researcher->insert_id;
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt_user = $conn->prepare("
                        INSERT INTO Users (username, password, email, role, researcher_id, is_active)
                        VALUES (?, ?, ?, ?, ?, 1)
                    ");
                    $stmt_user->bind_param("ssssi", $username, $hashed_password, $email, $signup_role, $researcher_id);

                    if ($stmt_user->execute()) {
                        $account_created = true;
                        $created_username = $username;
                        $success_message = "Account created successfully!";
                    } else {
                        $error_message = "Error creating user account.";
                    }
                    $stmt_user->close();
                } else {
                    $error_message = "Error creating researcher profile.";
                }
                $stmt_researcher->close();
            }
            $check_user->close();
        }
        $check_email->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo renderHead('Sign Up'); ?>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 30%, #312e81 60%, #4c1d95 100%); }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        .tab-active { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; }
        .tab-inactive { background: rgba(255,255,255,0.05); color: #9ca3af; }
    </style>
</head>
<body class="font-inter hero-gradient min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-primary-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-accent-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative z-10 w-full max-w-2xl fade-in-up py-8">
        <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Home
        </a>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-8 pb-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-bold mb-1">Create Account</h1>
                <p class="text-gray-400 text-sm">Join the research grant platform</p>
            </div>

            <?php if ($account_created): ?>
                <div class="px-8 pb-8">
                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-green-400 font-bold text-lg mb-2">Account Created!</h3>
                        <p class="text-gray-400 text-sm mb-1">Your username: <span class="text-white font-bold"><?php echo clean($created_username); ?></span></p>
                        <a href="login.php" class="inline-flex items-center gap-2 mt-4 px-6 py-2.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
                            Continue to Login
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="px-8 pb-8">
                    <!-- Role Selection -->
                    <div class="mb-6">
                        <div class="flex bg-white/5 rounded-xl p-1 gap-1">
                            <button type="button" onclick="setRole('user')" id="role-user" class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-active">
                                Researcher
                            </button>
                            <button type="button" onclick="setRole('reviewer')" id="role-reviewer" class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 tab-inactive hover:bg-white/10">
                                Reviewer
                            </button>
                        </div>
                        <input type="hidden" name="signup_role" id="signup_role" value="user">
                    </div>

                    <?php if (!empty($error_message)): ?>
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                            <p class="text-red-400 text-sm font-medium"><?php echo clean($error_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">First Name *</label>
                            <input type="text" name="first_name" required id="signup-first-name"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="First name">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Last Name *</label>
                            <input type="text" name="last_name" required id="signup-last-name"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Last name">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                            <input type="email" name="email" required id="signup-email"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="your@email.com">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Phone</label>
                            <input type="text" name="phone" id="signup-phone"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Phone number">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Institution</label>
                            <input type="text" name="institution" id="signup-institution"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="University/Institute">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Department</label>
                            <input type="text" name="department" id="signup-department"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Department">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Research Area</label>
                        <input type="text" name="research_area" id="signup-research-area"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                            placeholder="e.g., Artificial Intelligence, Biotechnology">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Username *</label>
                            <input type="text" name="username" required id="signup-username"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Choose username">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Password *</label>
                            <input type="password" name="password" required id="signup-password"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                                placeholder="Min 6 characters">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Confirm Password *</label>
                        <input type="password" name="confirm_password" required id="signup-confirm-password"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all text-sm"
                            placeholder="Re-enter password">
                    </div>

                    <button type="submit" name="signup" id="signup-btn"
                        class="w-full py-3.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary-500/25 transition-all duration-300 text-sm">
                        Create Account
                    </button>

                    <div class="mt-6 text-center">
                        <p class="text-gray-500 text-sm">
                            Already have an account? 
                            <a href="login.php" class="text-primary-400 hover:text-primary-300 font-semibold">Sign In</a>
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function setRole(role) {
        document.getElementById('signup_role').value = role;
        const userTab = document.getElementById('role-user');
        const reviewerTab = document.getElementById('role-reviewer');
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
