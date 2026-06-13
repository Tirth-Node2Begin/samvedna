<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/functions.php';

start_app_session();

if (isset($_GET['logout'])) {
    logout_admin();
    redirect_to('/');
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $loginError = 'The login form expired. Please try again.';
    } else {
        $username = post_value('username');
        $password = post_value('password');

        if (login_admin($username, $password)) {
            redirect_to('/admin/');
        }

        $loginError = 'Invalid admin username or password.';
    }
}

if (admin_is_logged_in() && isset($_GET['export'])) {
    export_inquiries_csv(read_inquiries());
}

$inquiries = admin_is_logged_in() ? read_inquiries() : [];
$today = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
$todayCount = 0;

foreach ($inquiries as $inquiry) {
    try {
        $created = new DateTime((string) ($inquiry['created_at'] ?? ''));
        $created->setTimezone(new DateTimeZone('Asia/Kolkata'));
        if ($created->format('Y-m-d') === $today) {
            $todayCount++;
        }
    } catch (Exception $exception) {
        continue;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - <?php echo h(APP_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0c4f2f',
                        'primary-light': '#1a6b42',
                        accent: '#d4af37',
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">

    <?php if (!admin_is_logged_in()): ?>
        <!-- Login Page -->
        <div class="min-h-screen w-full flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
                <div>
                    <div class="h-16 w-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2 class="mt-2 text-center text-3xl font-extrabold text-primary">
                        Admin Login
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-500">
                        Sign in to view consultation inquiries
                    </p>
                </div>
                <form class="mt-8 space-y-6" method="post" action="/admin/">
                    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                    
                    <?php if ($loginError !== ''): ?>
                        <div class="rounded-lg bg-red-50 p-4 border border-red-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800"><?php echo h($loginError); ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-md space-y-5">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input id="username" name="username" type="text" autocomplete="username" required class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors sm:text-sm mt-1">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors sm:text-sm mt-1">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-primary hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all shadow-lg shadow-primary/30 active:scale-[0.98]">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                                <i class="fas fa-lock text-primary-light group-hover:text-white transition-colors"></i>
                            </span>
                            Sign in to Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        
        <!-- Sidebar -->
        <aside class="w-64 bg-white text-gray-600 flex flex-col hidden md:flex flex-shrink-0 shadow-lg z-20 relative border-r border-gray-200">
            <div class="h-20 flex items-center px-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-primary/10 rounded flex items-center justify-center border border-primary/20">
                        <i class="fas fa-leaf text-primary"></i>
                    </div>
                    <span class="text-sm font-black tracking-widest text-gray-900 uppercase leading-tight">
                        Samvedna<br><span class="text-primary">Admin</span>
                    </span>
                </div>
            </div>
            
            <div class="px-4 pt-6 pb-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2">Menu</p>
            </div>
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-primary/10 text-primary rounded-xl font-bold transition-colors">
                    <i class="fas fa-inbox w-5 text-center"></i>
                    Inquiries
                    <span class="ml-auto bg-primary text-white py-0.5 px-2 rounded-full text-xs font-bold"><?php echo count($inquiries); ?></span>
                </a>
                <a href="/" target="_blank" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-primary rounded-xl font-medium transition-colors group">
                    <i class="fas fa-external-link-alt w-5 text-center group-hover:text-primary transition-colors"></i>
                    Live Website
                </a>
            </nav>
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3 px-4 py-3 mb-2 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="h-9 w-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                        AD
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Administrator</p>
                        <p class="text-xs text-gray-500">admin user</p>
                    </div>
                </div>
                <a href="/admin/?logout=1" class="flex items-center justify-center gap-2 w-full py-2.5 text-red-600 hover:bg-red-50 hover:text-red-700 rounded-lg font-medium transition-colors border border-transparent hover:border-red-100">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50/50">
            <!-- Header -->
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 shadow-sm flex-shrink-0 z-10">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    Dashboard Overview
                </h1>
                <div class="flex items-center gap-4">
                    <a href="/admin/?export=1" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 hover:text-primary transition-all shadow-sm active:scale-95">
                        <i class="fas fa-file-csv text-green-600"></i>
                        Export CSV
                    </a>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-auto p-6 md:p-8">
                
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Inquiries -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200/60 flex items-center gap-5 relative overflow-hidden group hover:border-primary/20 transition-colors">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Inquiries</p>
                            <p class="text-3xl font-black text-gray-800"><?php echo count($inquiries); ?></p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-5 text-blue-600 text-8xl pointer-events-none">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>

                    <!-- Today -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200/60 flex items-center gap-5 relative overflow-hidden group hover:border-primary/20 transition-colors">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Received Today</p>
                            <p class="text-3xl font-black text-gray-800"><?php echo $todayCount; ?></p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-5 text-emerald-600 text-8xl pointer-events-none">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>

                    <!-- Latest -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200/60 flex items-center gap-5 relative overflow-hidden group hover:border-primary/20 transition-colors">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Latest Entry</p>
                            <p class="text-lg font-bold text-gray-800 leading-tight">
                                <?php echo $inquiries === [] ? 'No entries' : h(formatted_date((string) $inquiries[0]['created_at'])); ?>
                            </p>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-5 text-purple-600 text-8xl pointer-events-none">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white">
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-list-ul text-primary/70"></i>
                                Recent Consultation Requests
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Review and manage inquiries submitted by families.</p>
                        </div>
                    </div>

                    <?php if ($inquiries === []): ?>
                        <div class="p-16 text-center bg-gray-50/30">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 text-gray-400 mb-5 text-3xl shadow-inner">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">No inquiries found</h3>
                            <p class="mt-2 text-gray-500 max-w-sm mx-auto">When families submit the consultation form on the website, their details will appear here automatically.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[1000px]">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200">
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-[180px]">Date & Time</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-[220px]">Parent & Child Info</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-[250px]">Contact Details</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-[200px]">Condition</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[250px]">Message</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($inquiries as $inquiry): ?>
                                        <tr class="hover:bg-blue-50/30 transition-colors group">
                                            
                                            <!-- Date Column -->
                                            <td class="px-6 py-5 align-top">
                                                <div class="text-sm font-bold text-gray-800 bg-gray-100/80 inline-block px-2 py-1 rounded">
                                                    <?php echo h(formatted_date((string) ($inquiry['created_at'] ?? ''))); ?>
                                                </div>
                                                <div class="text-[11px] text-gray-400 mt-2 font-mono uppercase tracking-wider">
                                                    Ref ID: #<?php echo substr(h((string) ($inquiry['id'] ?? '')), 0, 8); ?>
                                                </div>
                                                
                                                <?php 
                                                    $time = strtolower((string) ($inquiry['preferred_time'] ?? ''));
                                                    $timeIcon = 'fa-sun text-orange-400';
                                                    $timeColor = 'bg-orange-50 text-orange-700 border-orange-100';
                                                    
                                                    if ($time === 'afternoon') {
                                                        $timeIcon = 'fa-cloud-sun text-blue-400';
                                                        $timeColor = 'bg-blue-50 text-blue-700 border-blue-100';
                                                    } elseif ($time === 'evening') {
                                                        $timeIcon = 'fa-moon text-indigo-400';
                                                        $timeColor = 'bg-indigo-50 text-indigo-700 border-indigo-100';
                                                    }
                                                ?>
                                                <div class="mt-3 inline-flex items-center px-2 py-1 rounded-md text-xs font-bold <?php echo $timeColor; ?> border shadow-sm">
                                                    <i class="fas <?php echo $timeIcon; ?> mr-1.5"></i>
                                                    <?php echo h(ucfirst($time)); ?>
                                                </div>
                                            </td>

                                            <!-- Parent & Child Column -->
                                            <td class="px-6 py-5 align-top">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5">
                                                        <?php echo strtoupper(substr((string) ($inquiry['parent_name'] ?? 'U'), 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-900"><?php echo h((string) ($inquiry['parent_name'] ?? '')); ?></div>
                                                        <div class="text-sm text-gray-500 mt-1 flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded-md inline-flex border border-gray-100">
                                                            <i class="fas fa-child text-primary/60"></i>
                                                            <span class="font-medium text-gray-700"><?php echo h((string) ($inquiry['child_age'] ?? '')); ?></span> yrs old
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Contact Column -->
                                            <td class="px-6 py-5 align-top">
                                                <div class="text-sm flex flex-col gap-2.5">
                                                    <a href="mailto:<?php echo h((string) ($inquiry['email'] ?? '')); ?>" class="text-gray-600 hover:text-primary flex items-center gap-2.5 group/link transition-colors w-fit">
                                                        <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-400 group-hover/link:bg-primary/10 group-hover/link:text-primary transition-colors">
                                                            <i class="fas fa-envelope text-xs"></i>
                                                        </div>
                                                        <span class="truncate max-w-[180px] font-medium" title="<?php echo h((string) ($inquiry['email'] ?? '')); ?>">
                                                            <?php echo h((string) ($inquiry['email'] ?? '')); ?>
                                                        </span>
                                                    </a>
                                                    <a href="tel:<?php echo h((string) ($inquiry['phone'] ?? '')); ?>" class="text-gray-600 hover:text-primary flex items-center gap-2.5 group/link transition-colors w-fit">
                                                        <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-400 group-hover/link:bg-primary/10 group-hover/link:text-primary transition-colors">
                                                            <i class="fas fa-phone-alt text-xs"></i>
                                                        </div>
                                                        <span class="font-medium"><?php echo h((string) ($inquiry['phone'] ?? '')); ?></span>
                                                    </a>
                                                    <div class="text-gray-500 flex items-center gap-2.5 w-fit">
                                                        <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                                            <i class="fas fa-map-marker-alt text-xs"></i>
                                                        </div>
                                                        <span class="font-medium"><?php echo h((string) ($inquiry['country'] ?? '')); ?></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Condition Column -->
                                            <td class="px-6 py-5 align-top">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-primary/5 text-primary border border-primary/20 shadow-sm leading-tight max-w-full">
                                                    <?php echo h((string) ($inquiry['condition'] ?? '')); ?>
                                                </span>
                                            </td>

                                            <!-- Message Column -->
                                            <td class="px-6 py-5 align-top">
                                                <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                                    <?php echo empty($inquiry['message']) ? '<span class="text-gray-400 italic">No message provided</span>' : h((string) ($inquiry['message'] ?? '')); ?>
                                                </div>
                                            </td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-8 text-center text-sm font-medium text-gray-400 pb-8 flex items-center justify-center gap-2">
                    <i class="fas fa-shield-alt text-gray-300"></i>
                    &copy; <?php echo date('Y'); ?> <?php echo h(APP_NAME); ?> Admin Portal
                </div>
            </div>
        </main>
    <?php endif; ?>
</body>
</html>
