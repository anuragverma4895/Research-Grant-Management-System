<?php
require_once 'config.php';
session_start();

// Redirect if logged in
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : ($_SESSION['role'] === 'reviewer' ? "reviewer_dashboard.php" : "user_dashboard.php")));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once 'functions.php'; echo renderHead('Home'); ?>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 30%, #312e81 60%, #4c1d95 100%); }
        .card-glow:hover { box-shadow: 0 0 40px rgba(99, 102, 241, 0.15); }
        .float-animation { animation: float 6s ease-in-out infinite; }
        .float-animation-delayed { animation: float 6s ease-in-out infinite 2s; }
        @keyframes float { 0%,100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
        .fade-in-up-d1 { animation: fadeInUp 0.8s ease-out 0.1s forwards; opacity:0; }
        .fade-in-up-d2 { animation: fadeInUp 0.8s ease-out 0.2s forwards; opacity:0; }
        .fade-in-up-d3 { animation: fadeInUp 0.8s ease-out 0.3s forwards; opacity:0; }
        .fade-in-up-d4 { animation: fadeInUp 0.8s ease-out 0.4s forwards; opacity:0; }
        .gradient-text { background: linear-gradient(135deg, #818cf8, #c084fc, #f472b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        .particle { position: absolute; border-radius: 50%; background: rgba(99, 102, 241, 0.3); pointer-events: none; }
    </style>
</head>
<body class="font-inter hero-gradient min-h-screen relative overflow-x-hidden">
    
    <!-- Animated Background Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="particle w-2 h-2 top-[10%] left-[10%] float-animation opacity-50"></div>
        <div class="particle w-3 h-3 top-[20%] right-[20%] float-animation-delayed opacity-30"></div>
        <div class="particle w-1.5 h-1.5 top-[60%] left-[30%] float-animation opacity-40"></div>
        <div class="particle w-2.5 h-2.5 top-[80%] right-[10%] float-animation-delayed opacity-20"></div>
        <div class="particle w-2 h-2 top-[40%] left-[70%] float-animation opacity-35"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-primary-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-accent-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="flex items-center justify-between px-6 lg:px-12 py-5 fade-in-up">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center shadow-lg shadow-primary-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg hidden sm:block">RGMS</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="#features" class="text-gray-400 hover:text-white px-3 py-2 text-sm font-medium transition-colors hidden md:block">Features</a>
                <a href="#how-it-works" class="text-gray-400 hover:text-white px-3 py-2 text-sm font-medium transition-colors hidden md:block">How It Works</a>
                <a href="login.php" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-colors">Login</a>
                <a href="signup.php" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-full text-sm font-medium transition-all border border-white/10">Sign Up</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-1 flex items-center justify-center px-6 lg:px-12 py-12">
            <div class="max-w-6xl w-full">
                <div class="text-center mb-16">
                    <div class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-full px-4 py-1.5 mb-8 fade-in-up">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-gray-300 text-xs font-medium tracking-wide">v<?php echo APP_VERSION; ?> — Now Live</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black text-white leading-tight mb-6 fade-in-up-d1">
                        Research Grant<br>
                        <span class="gradient-text">Management System</span>
                    </h1>
                    <p class="text-gray-400 text-lg lg:text-xl max-w-2xl mx-auto mb-10 fade-in-up-d2">
                        Streamline your research funding journey. Apply, review, and manage grants with an intelligent platform designed for academic excellence.
                    </p>
                </div>

                <!-- Portal Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    
                    <!-- Researcher Portal -->
                    <a href="login.php" id="researcher-portal" class="glass-card rounded-2xl p-8 text-center group hover:bg-white/10 transition-all duration-500 card-glow cursor-pointer fade-in-up-d2">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center mx-auto mb-5 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-primary-500/30 transition-all duration-500">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Researcher</h3>
                        <p class="text-gray-400 text-sm mb-5 leading-relaxed">Apply for grants and track your research proposals</p>
                        <span class="inline-flex items-center gap-2 text-primary-400 text-sm font-semibold group-hover:gap-3 transition-all">
                            Enter Portal
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </a>

                    <!-- Reviewer Portal -->
                    <a href="login.php?role=reviewer" id="reviewer-portal" class="glass-card rounded-2xl p-8 text-center group hover:bg-white/10 transition-all duration-500 card-glow cursor-pointer fade-in-up-d3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center mx-auto mb-5 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-emerald-500/30 transition-all duration-500">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Reviewer</h3>
                        <p class="text-gray-400 text-sm mb-5 leading-relaxed">Evaluate and score research proposals</p>
                        <span class="inline-flex items-center gap-2 text-emerald-400 text-sm font-semibold group-hover:gap-3 transition-all">
                            Enter Portal
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </a>

                    <!-- Admin Portal -->
                    <a href="admin_login.php" id="admin-portal" class="glass-card rounded-2xl p-8 text-center group hover:bg-white/10 transition-all duration-500 card-glow cursor-pointer fade-in-up-d4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-rose-700 flex items-center justify-center mx-auto mb-5 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-red-500/30 transition-all duration-500">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">Admin</h3>
                        <p class="text-gray-400 text-sm mb-5 leading-relaxed">Manage the entire grant ecosystem</p>
                        <span class="inline-flex items-center gap-2 text-red-400 text-sm font-semibold group-hover:gap-3 transition-all">
                            Enter Portal
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </a>
                </div>

                <!-- Stats Bar -->
                <div class="mt-16 flex flex-wrap items-center justify-center gap-8 lg:gap-16 fade-in-up-d4">
                    <div class="text-center">
                        <p class="text-2xl lg:text-3xl font-black text-white">500+</p>
                        <p class="text-gray-500 text-xs font-medium mt-1">Applications</p>
                    </div>
                    <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-2xl lg:text-3xl font-black text-white">₹50M+</p>
                        <p class="text-gray-500 text-xs font-medium mt-1">Funding Distributed</p>
                    </div>
                    <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-2xl lg:text-3xl font-black text-white">200+</p>
                        <p class="text-gray-500 text-xs font-medium mt-1">Researchers</p>
                    </div>
                    <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-2xl lg:text-3xl font-black text-white">50+</p>
                        <p class="text-gray-500 text-xs font-medium mt-1">Agencies</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section class="px-6 lg:px-12 py-24 border-t border-white/5" id="features">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-primary-400 text-sm font-semibold tracking-widest uppercase">What We Offer</span>
                    <h2 class="text-3xl lg:text-5xl font-black text-white mt-3">Powerful Features</h2>
                    <p class="text-gray-400 mt-4 max-w-xl mx-auto">Everything you need to manage research grants from submission to approval.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">Online Applications</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Submit grant proposals digitally with PDF uploads, budget breakdowns, and project timelines.</p>
                    </div>
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">Peer Review System</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Expert reviewers evaluate proposals with scoring rubrics and detailed recommendations.</p>
                    </div>
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">Real-Time Analytics</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Interactive Chart.js dashboards showing funding trends, approval rates, and monthly reports.</p>
                    </div>
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">Role-Based Access</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Separate portals for Admins, Researchers, and Reviewers with dedicated dashboards and tools.</p>
                    </div>
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">Search & Filter</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Find any application instantly with powerful search by name, title, status, or funding agency.</p>
                    </div>
                    <div class="glass-card rounded-2xl p-7 hover:bg-white/10 transition-all duration-300 group scroll-reveal">
                        <div class="w-12 h-12 rounded-xl bg-violet-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></div>
                        <h3 class="text-white font-bold text-lg mb-2">REST API</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Built-in JSON API endpoints for external integrations with secure API key authentication.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="px-6 lg:px-12 py-24 border-t border-white/5" id="how-it-works">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-emerald-400 text-sm font-semibold tracking-widest uppercase">Simple Process</span>
                    <h2 class="text-3xl lg:text-5xl font-black text-white mt-3">How It Works</h2>
                    <p class="text-gray-400 mt-4 max-w-xl mx-auto">From application to approval — a streamlined 4-step process.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center scroll-reveal">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center mx-auto mb-5 text-white text-2xl font-black shadow-lg shadow-primary-500/20">1</div>
                        <h3 class="text-white font-bold mb-2">Register</h3>
                        <p class="text-gray-400 text-sm">Create your researcher or reviewer account in seconds.</p>
                    </div>
                    <div class="text-center scroll-reveal">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center mx-auto mb-5 text-white text-2xl font-black shadow-lg shadow-emerald-500/20">2</div>
                        <h3 class="text-white font-bold mb-2">Apply</h3>
                        <p class="text-gray-400 text-sm">Submit your grant proposal with budget, timeline, and PDF uploads.</p>
                    </div>
                    <div class="text-center scroll-reveal">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500 to-orange-700 flex items-center justify-center mx-auto mb-5 text-white text-2xl font-black shadow-lg shadow-amber-500/20">3</div>
                        <h3 class="text-white font-bold mb-2">Review</h3>
                        <p class="text-gray-400 text-sm">Expert reviewers evaluate and score your research proposal.</p>
                    </div>
                    <div class="text-center scroll-reveal">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-rose-500 to-pink-700 flex items-center justify-center mx-auto mb-5 text-white text-2xl font-black shadow-lg shadow-rose-500/20">4</div>
                        <h3 class="text-white font-bold mb-2">Get Funded</h3>
                        <p class="text-gray-400 text-sm">Approved grants receive funding and track progress in real-time.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <section class="px-6 lg:px-12 py-24 border-t border-white/5" id="tech">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-rose-400 text-sm font-semibold tracking-widest uppercase">Built With</span>
                    <h2 class="text-3xl lg:text-5xl font-black text-white mt-3">Technology Stack</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="glass-card rounded-2xl p-6 text-center hover:bg-white/10 transition-all scroll-reveal">
                        <div class="text-3xl mb-3">🐘</div><p class="text-white font-bold text-sm">PHP 8.x</p><p class="text-gray-500 text-xs mt-1">Backend</p>
                    </div>
                    <div class="glass-card rounded-2xl p-6 text-center hover:bg-white/10 transition-all scroll-reveal">
                        <div class="text-3xl mb-3">🗃️</div><p class="text-white font-bold text-sm">MySQL</p><p class="text-gray-500 text-xs mt-1">Database</p>
                    </div>
                    <div class="glass-card rounded-2xl p-6 text-center hover:bg-white/10 transition-all scroll-reveal">
                        <div class="text-3xl mb-3">🎨</div><p class="text-white font-bold text-sm">Tailwind CSS</p><p class="text-gray-500 text-xs mt-1">Styling</p>
                    </div>
                    <div class="glass-card rounded-2xl p-6 text-center hover:bg-white/10 transition-all scroll-reveal">
                        <div class="text-3xl mb-3">📊</div><p class="text-white font-bold text-sm">Chart.js</p><p class="text-gray-500 text-xs mt-1">Analytics</p>
                    </div>
                    <div class="glass-card rounded-2xl p-6 text-center hover:bg-white/10 transition-all scroll-reveal">
                        <div class="text-3xl mb-3">🔗</div><p class="text-white font-bold text-sm">REST API</p><p class="text-gray-500 text-xs mt-1">Integration</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="px-6 lg:px-12 py-24 border-t border-white/5">
            <div class="max-w-3xl mx-auto text-center scroll-reveal">
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-4">Ready to Get Started?</h2>
                <p class="text-gray-400 mb-8 text-lg">Join researchers and institutions already using RGMS to manage their grants efficiently.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="signup.php" class="px-8 py-3.5 bg-gradient-to-r from-primary-500 to-accent-600 text-white rounded-full font-semibold text-sm hover:shadow-xl hover:shadow-primary-500/20 transition-all">Create Free Account</a>
                    <a href="login.php" class="px-8 py-3.5 bg-white/5 border border-white/10 text-white rounded-full font-semibold text-sm hover:bg-white/10 transition-all">Login to Dashboard</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/5 px-6 lg:px-12 py-10">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <span class="text-white font-bold text-sm"><?php echo APP_NAME; ?></span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#features" class="text-gray-500 hover:text-white text-sm transition-colors">Features</a>
                    <a href="#how-it-works" class="text-gray-500 hover:text-white text-sm transition-colors">How It Works</a>
                    <a href="#tech" class="text-gray-500 hover:text-white text-sm transition-colors">Tech Stack</a>
                </div>
                <p class="text-gray-600 text-xs">&copy; <?php echo date('Y'); ?> Built by Anurag Verma</p>
            </div>
        </footer>
    </div>

    <script>
    // Scroll Reveal Animation
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if(e.isIntersecting) { e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; } });
    }, {threshold:0.1});
    document.querySelectorAll('.scroll-reveal').forEach(el => {
        el.style.opacity='0'; el.style.transform='translateY(30px)';
        el.style.transition='opacity 0.6s ease, transform 0.6s ease';
        obs.observe(el);
    });
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => { e.preventDefault(); document.querySelector(a.getAttribute('href'))?.scrollIntoView({behavior:'smooth'}); });
    });
    </script>
    <script src="script.js"></script>
</body>
</html>