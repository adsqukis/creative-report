<?php
// views/components/pagination.php
// Usage: require with $paginator set
if (!isset($paginator) || !$paginator->hasPages()) return;
?>
<div class="flex items-center justify-between px-1 py-3">
    <p class="text-xs text-gray-400">Menampilkan <?= $paginator->showing() ?></p>
    <div class="flex items-center gap-1">
        <?php if ($paginator->currentPage > 1): ?>
        <a href="<?= $paginator->url(1) ?>" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">&laquo;</a>
        <a href="<?= $paginator->url($paginator->currentPage - 1) ?>" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">&lsaquo;</a>
        <?php endif; ?>

        <?php foreach ($paginator->pages() as $pg): ?>
        <a href="<?= $paginator->url($pg) ?>"
            class="px-2.5 py-1.5 text-xs border rounded-lg <?= $pg === $paginator->currentPage ? 'bg-violet-600 text-white border-violet-600 font-semibold' : 'border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
            <?= $pg ?>
        </a>
        <?php endforeach; ?>

        <?php if ($paginator->currentPage < $paginator->lastPage): ?>
        <a href="<?= $paginator->url($paginator->currentPage + 1) ?>" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">&rsaquo;</a>
        <a href="<?= $paginator->url($paginator->lastPage) ?>" class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">&raquo;</a>
        <?php endif; ?>
    </div>
</div>
