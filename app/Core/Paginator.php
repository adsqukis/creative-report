<?php
class Paginator {
    public int $total;
    public int $perPage;
    public int $currentPage;
    public int $lastPage;
    public int $offset;

    public function __construct(int $total, int $perPage = 25, ?int $page = null) {
        $this->total       = $total;
        $this->perPage     = max(1, $perPage);
        $this->currentPage = max(1, $page ?? (int)($_GET['page'] ?? 1));
        $this->lastPage    = max(1, (int)ceil($total / $this->perPage));
        $this->currentPage = min($this->currentPage, $this->lastPage);
        $this->offset      = ($this->currentPage - 1) * $this->perPage;
    }

    public function hasPages(): bool {
        return $this->lastPage > 1;
    }

    public function url(int $page): string {
        $params        = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }

    public function pages(): array {
        $pages = [];
        $start = max(1, $this->currentPage - 2);
        $end   = min($this->lastPage, $this->currentPage + 2);
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        return $pages;
    }

    public function showing(): string {
        $from = min($this->total, $this->offset + 1);
        $to   = min($this->total, $this->offset + $this->perPage);
        return $from . '–' . $to . ' dari ' . $this->total;
    }
}
