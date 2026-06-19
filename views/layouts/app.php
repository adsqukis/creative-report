<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — Creative Ops</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: window.innerWidth >= 768, notifOpen: false }" @resize.window="sidebarOpen = window.innerWidth >= 768">
<div class="flex h-screen overflow-hidden relative">

    <!-- Mobile overlay backdrop -->
    <div
        x-show="sidebarOpen && window.innerWidth < 768"
        x-cloak
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 z-20 md:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
        class="fixed md:relative z-30 md:z-auto inset-y-0 left-0 w-64 md:w-60 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 shrink-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0 md:hidden'"
        x-cloak
    >
        <!-- Logo -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 bg-violet-600 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="font-semibold text-gray-900 text-sm">Creative Ops</span>
            </div>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
            <?php $cp = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>

            <?php
            $active = 'flex items-center gap-2.5 px-3 py-2 rounded-md font-medium text-violet-700 bg-violet-50';
            $normal = 'flex items-center gap-2.5 px-3 py-2 rounded-md font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900';
            $icon   = 'w-4 h-4 shrink-0';
            ?>

            <a href="<?= APP_URL ?>/dashboard" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= (strpos($cp, '/dashboard') !== false || $cp === '/') ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Overview
            </a>

            <a href="<?= APP_URL ?>/requests" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/requests') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Requests
            </a>

            <?php if (Auth::hasRole(['designer', 'video_editor'])): ?>
            <a href="<?= APP_URL ?>/workspace" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/workspace') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                My Workspace
            </a>
            <?php endif; ?>

            <?php if (Auth::hasRole(['super_admin', 'creative_manager'])): ?>
            <a href="<?= APP_URL ?>/workload" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/workload') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                Workload
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Analytics</p>
            </div>
            <a href="<?= APP_URL ?>/analytics" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= $cp === '/analytics' ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analytics
            </a>
            <a href="<?= APP_URL ?>/analytics/sla" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/analytics/sla') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                SLA Tracking
            </a>
            <a href="<?= APP_URL ?>/analytics/scorecard" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/scorecard') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Scorecard
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">AI Module</p>
            </div>
            <a href="<?= APP_URL ?>/ai/insights" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/ai/insights') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                AI Insights
            </a>
            <a href="<?= APP_URL ?>/ai/briefing" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/ai/briefing') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                Daily Briefing
            </a>
            <a href="<?= APP_URL ?>/ai/chat" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/ai/chat') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                AI Chat
            </a>
            <a href="<?= APP_URL ?>/executive" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/executive') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                Command Center
            </a>
            <?php endif; ?>

            <?php if (Auth::hasRole(['super_admin'])): ?>
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
            </div>
            <a href="<?= APP_URL ?>/users" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/users') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>
            <a href="<?= APP_URL ?>/products" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/products') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Products
            </a>
            <a href="<?= APP_URL ?>/campaigns" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/campaigns') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                Campaigns
            </a>
            <a href="<?= APP_URL ?>/departments" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/departments') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 6v-3a1 1 0 011-1h1a1 1 0 011 1v3"/></svg>
                Departments
            </a>
            <a href="<?= APP_URL ?>/settings" @click="if(window.innerWidth < 768) sidebarOpen = false" class="<?= strpos($cp, '/settings') !== false ? $active : $normal ?>">
                <svg class="<?= $icon ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
            <?php endif; ?>
        </nav>

        <!-- User chip -->
        <div class="p-3 border-t border-gray-100">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-md hover:bg-gray-50">
                <div class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-semibold text-xs shrink-0">
                    <?= strtoupper(substr(Auth::user()['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-900 truncate"><?= htmlspecialchars(Auth::user()['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 capitalize truncate"><?= str_replace('_', ' ', Auth::user()['role'] ?? '') ?></p>
                </div>
                <a href="<?= APP_URL ?>/logout" title="Logout" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-700 p-1 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <div class="ml-auto flex items-center gap-1.5 shrink-0">
                <!-- Notification bell -->
                <div class="relative" x-data="notifBell()" x-init="init()">
                    <button @click="toggle()" class="relative p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="count > 0" x-cloak x-text="count > 9 ? '9+' : count"
                            class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-0.5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"></span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute right-0 top-9 w-72 max-w-[calc(100vw-2rem)] bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs font-semibold text-gray-700">Notifikasi</p>
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                            <template x-if="items.length === 0">
                                <p class="px-4 py-6 text-xs text-gray-400 text-center">Tidak ada notifikasi baru.</p>
                            </template>
                            <template x-for="n in items" :key="n.id">
                                <a :href="n.request_id ? '<?= APP_URL ?>/requests/' + n.request_id : '#'"
                                   class="flex items-start gap-2.5 px-4 py-3 hover:bg-gray-50 block">
                                    <span class="mt-1.5 w-2 h-2 rounded-full shrink-0"
                                        :class="n.is_read == 0 ? 'bg-violet-500' : 'bg-transparent'"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-800 line-clamp-1" x-text="n.title"></p>
                                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                        <p class="text-xs text-gray-300 mt-0.5" x-text="n.created_at"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100">
                            <button @click="markRead()" class="text-xs text-violet-500 hover:text-violet-700">Tandai semua sudah dibaca</button>
                        </div>
                    </div>
                </div>

                <a href="<?= APP_URL ?>/profile" class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors" title="Profil">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </a>

                <a href="<?= APP_URL ?>/requests/create"
                    class="inline-flex items-center gap-1 bg-violet-600 text-white text-xs font-semibold px-2.5 py-2 rounded-lg hover:bg-violet-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">New Request</span>
                </a>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-5">
            <?php require APP_ROOT . '/views/' . ($view ?? 'dashboard/overview') . '.php'; ?>
        </main>
    </div>

</div>
<script>
function notifBell() {
    return {
        open: false,
        count: 0,
        items: [],
        init: function() {
            var self = this;
            self.fetch();
            setInterval(function() { self.fetch(); }, 30000);
        },
        fetch: function() {
            var self = this;
            fetch('<?= APP_URL ?>/api/v1/notifications')
                .then(function(r) { return r.json(); })
                .then(function(d) { self.count = d.unread || 0; self.items = d.items || []; })
                .catch(function() {});
        },
        toggle: function() {
            this.open = !this.open;
        },
        markRead: function() {
            var self = this;
            fetch('<?= APP_URL ?>/api/v1/notifications/read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '_token=<?= Csrf::token() ?>'
            })
            .then(function() {
                self.count = 0;
                self.items = self.items.map(function(n) { n.is_read = 1; return n; });
                self.open = false;
            })
            .catch(function() {});
        }
    };
}
</script>
</body>
</html>