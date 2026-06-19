<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>404 — Creative Ops</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="text-center">
    <p class="text-6xl font-bold text-gray-200 mb-4">404</p>
    <h1 class="text-lg font-semibold text-gray-700 mb-2">Halaman tidak ditemukan</h1>
    <p class="text-sm text-gray-400 mb-6">URL yang kamu akses tidak ada.</p>
    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard" class="inline-flex items-center gap-2 bg-violet-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-violet-700">
        Kembali ke Dashboard
    </a>
</div>
</body></html>
