<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Creative Ops</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-11 h-11 bg-violet-600 rounded-xl items-center justify-center mb-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-lg font-semibold text-gray-900">Creative Ops</h1>
            <p class="text-sm text-gray-400 mt-0.5">Creative Operations Control Tower</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-7">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Masuk ke akun kamu</h2>

            <?php if (!empty($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-5 text-sm text-red-700 flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/login">
                <?= Csrf::input() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                            placeholder="kamu@perusahaan.com" required autofocus>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                            placeholder="••••••••" required>
                    </div>
                    <button type="submit"
                        class="w-full bg-violet-600 text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-violet-700 active:bg-violet-800 transition-colors mt-1">
                        Masuk
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">Creative Ops v1.0 &mdash; Internal Use Only</p>
    </div>
</body>
</html>
