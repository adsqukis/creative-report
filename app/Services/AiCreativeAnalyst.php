<?php
class AiCreativeAnalyst {
    private static string $systemPrompt = 'Kamu adalah Creative Operations Analyst untuk perusahaan FMCG Indonesia. Tim kreatif memproduksi konten digital (desain, video) untuk suplemen anak: madu, herbal, susu, harga Rp 80k-400k. Berikan insight dalam Bahasa Indonesia, singkat, tajam, actionable. Response HANYA berupa JSON object dengan key: insight_text (string max 200 karakter), recommendation (string max 200 karakter), severity (string: info|warning|critical). Jangan tambahkan penjelasan lain, markdown, atau backtick.';

    private static string $chatSystemPrompt = 'Kamu adalah Creative Operations Assistant untuk tim FMCG Indonesia. Kamu memiliki akses ke data operasional tim kreatif. Jawab pertanyaan dalam Bahasa Indonesia, singkat dan tepat. Gunakan data konteks yang diberikan untuk menjawab. Jika data tidak tersedia, katakan dengan jelas.';

    private static function getProvider(): object {
        $cfg = require APP_ROOT . '/config/ai.php';
        if (($cfg['provider'] ?? 'null') === 'deepseek' && !empty($cfg['api_key']) && $cfg['api_key'] !== 'sk-your-deepseek-api-key-here') {
            return new DeepSeekProvider();
        }
        return new NullProvider();
    }

    private static function buildSnapshot(string $type): array {
        $db = Database::getInstance();

        if ($type === 'bottleneck') {
            $team    = WorkloadService::getTeamWorkload();
            $over    = array_values(array_filter($team, function($m) {
                return $m['is_overloaded'];
            }));
            $names   = array_map(function($m) {
                return $m['name'] . ' (' . $m['workload_pct'] . '%)';
            }, $over);

            $sql1    = "SELECT COUNT(*) FROM co_requests WHERE status = 'ready_review' AND updated_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)";
            $stuck   = $db->count($sql1, []);

            $sql2    = "SELECT COUNT(*) FROM co_requests WHERE revision_count >= 3 AND status NOT IN ('completed','cancelled','rejected')";
            $hiRev   = $db->count($sql2, []);

            $sql3    = "SELECT COUNT(*) FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 WHERE r.priority = 'critical' AND r.status IN ('draft','waiting_queue') AND a.id IS NULL";
            $unassigned = $db->count($sql3, []);

            return ['overloaded_designers' => $names, 'approval_stuck' => $stuck, 'high_revision_tasks' => $hiRev, 'unassigned_critical' => $unassigned];
        }

        if ($type === 'performance') {
            $ym      = date('Y-m');
            $sql1    = "SELECT COUNT(*) FROM co_requests WHERE DATE_FORMAT(created_at,'%Y-%m') = ?";
            $total   = $db->count($sql1, [$ym]);
            $sql2    = "SELECT COUNT(*) FROM co_requests WHERE status = 'completed' AND DATE_FORMAT(completed_at,'%Y-%m') = ?";
            $done    = $db->count($sql2, [$ym]);
            $sla     = SlaService::getMonthlyStats();
            $rate    = $total > 0 ? round(($done / $total) * 100, 1) : 0;
            return ['total_mtd' => $total, 'completed_mtd' => $done, 'completion_rate_pct' => $rate, 'sla_achievement_pct' => $sla['achievement_pct'], 'avg_turnaround_h' => $sla['avg_turnaround_h']];
        }

        if ($type === 'product') {
            $ym   = date('Y-m');
            $sql  = "SELECT p.name, COUNT(r.id) as cnt FROM co_requests r JOIN co_products p ON r.product_id = p.id WHERE DATE_FORMAT(r.created_at,'%Y-%m') = ? GROUP BY p.id ORDER BY cnt DESC LIMIT 5";
            $rows = $db->query($sql, [$ym]);
            $top  = !empty($rows) ? $rows[0]['name'] : '';
            return ['top_product' => $top, 'products' => $rows, 'period' => $ym];
        }

        if ($type === 'priority_rec') {
            $sql1 = "SELECT COUNT(*) FROM co_requests WHERE priority = 'critical' AND deadline = CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
            $crit = $db->count($sql1, []);
            $sql2 = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
            $over = $db->count($sql2, []);
            return ['critical_today' => $crit, 'overdue_total' => $over, 'date' => date('Y-m-d')];
        }

        if ($type === 'forecast') {
            $sql1    = "SELECT WEEK(created_at) as wk, COUNT(*) as cnt FROM co_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 4 WEEK) GROUP BY WEEK(created_at) ORDER BY wk ASC";
            $weekly  = $db->query($sql1, []);
            $counts  = array_column($weekly, 'cnt');
            $avg     = count($counts) > 0 ? round(array_sum($counts) / count($counts)) : 0;
            $sql2    = "SELECT COUNT(*) FROM co_users WHERE role IN ('designer','video_editor') AND is_active = 1";
            $members = $db->count($sql2, []);
            return ['forecast_next_week' => $avg, 'team_size' => $members, 'team_capacity_tasks' => $members * 5];
        }

        if ($type === 'team_ranking') {
            $ym   = date('Y-m');
            $sql  = "SELECT u.name, k.creative_score, k.sla_score, k.completion_score, k.revision_score FROM co_designer_kpi_monthly k JOIN co_users u ON k.user_id = u.id WHERE k.year_month = ? ORDER BY k.creative_score DESC LIMIT 5";
            $rows = $db->query($sql, [$ym]);
            return ['rankings' => $rows, 'period' => $ym];
        }

        return [];
    }

    public static function generateInsight(string $type): bool {
        $db       = Database::getInstance();
        $snap     = self::buildSnapshot($type);
        $provider = self::getProvider();
        $isDet    = 0;
        $result   = null;

        if ($provider instanceof DeepSeekProvider) {
            $snapJson   = json_encode($snap, JSON_UNESCAPED_UNICODE);
            $userPrompt = 'Tipe analisis: ' . $type . PHP_EOL . 'Data: ' . $snapJson;
            $raw        = $provider->chat(self::$systemPrompt, $userPrompt);

            if ($raw !== null) {
                $clean  = trim(str_replace(['```json', '```'], '', $raw));
                $parsed = json_decode($clean, true);
                if (is_array($parsed) && isset($parsed['insight_text'])) {
                    $result = $parsed;
                }
            }
        }

        if ($result === null) {
            $null   = new NullProvider();
            $result = $null->generateInsight($type, $snap);
            $isDet  = 1;
        }

        $expires = (new DateTime())->modify('+6 hours')->format('Y-m-d H:i:s');
        $titles  = [
            'bottleneck'   => 'Bottleneck Detection',
            'performance'  => 'Performance Analysis',
            'product'      => 'Product Analysis',
            'priority_rec' => 'Priority Recommendation',
            'forecast'     => 'Demand Forecast',
            'team_ranking' => 'Team Ranking',
        ];
        $title   = $titles[$type] ?? ucfirst($type);

        $sql = "INSERT INTO co_ai_insights (insight_type, title, insight_text, data_snapshot, recommendation, severity, expires_at, is_deterministic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db->execute($sql, [
            $type, $title,
            $result['insight_text']   ?? '',
            json_encode($snap),
            $result['recommendation'] ?? '',
            $result['severity']       ?? 'info',
            $expires,
            $isDet,
        ]);

        return true;
    }

    public static function generateDailyBriefing(): bool {
        $db     = Database::getInstance();
        $today  = date('Y-m-d');

        $sql1   = "SELECT COUNT(*) FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected') AND deadline = CURDATE()";
        $todayT = $db->count($sql1, []);

        $sql2   = "SELECT COUNT(*) FROM co_requests WHERE priority = 'critical' AND status NOT IN ('completed','cancelled','rejected')";
        $crit   = $db->count($sql2, []);

        $sql3   = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $over   = $db->count($sql3, []);

        $team      = WorkloadService::getTeamWorkload();
        $designerOL = count(array_filter($team, function($m) {
            return $m['is_overloaded'] && $m['role'] === 'designer';
        }));
        $editorOL   = count(array_filter($team, function($m) {
            return $m['is_overloaded'] && $m['role'] === 'video_editor';
        }));

        $sql4 = "SELECT r.request_number, r.title, r.priority, r.deadline FROM co_requests r WHERE r.status NOT IN ('completed','cancelled','rejected') ORDER BY r.priority_score DESC LIMIT 5";
        $tops = $db->query($sql4, []);

        $topsJson = json_encode($tops, JSON_UNESCAPED_UNICODE);

        $lines  = ['*Creative Ops Daily Briefing* — ' . date('d M Y')];
        $lines[] = '';
        $lines[] = 'Total task deadline hari ini: ' . $todayT;
        $lines[] = 'Critical terbuka: ' . $crit;
        $lines[] = 'Overdue: ' . $over;
        if ($designerOL > 0) {
            $lines[] = 'Designer overload: ' . $designerOL . ' orang';
        }
        if ($editorOL > 0) {
            $lines[] = 'Editor overload: ' . $editorOL . ' orang';
        }

        $briefingText = implode(PHP_EOL, $lines);

        $provider = self::getProvider();
        $aiRecs   = '';

        if ($provider instanceof DeepSeekProvider) {
            $snapJson = json_encode(['today_tasks' => $todayT, 'critical' => $crit, 'overdue' => $over, 'designer_overload' => $designerOL, 'editor_overload' => $editorOL], JSON_UNESCAPED_UNICODE);
            $raw      = $provider->chat(self::$systemPrompt, 'Buat rekomendasi tindakan harian berdasarkan data ini: ' . $snapJson);
            if ($raw !== null) {
                $clean  = trim(str_replace(['```json', '```'], '', $raw));
                $parsed = json_decode($clean, true);
                if (is_array($parsed)) {
                    $aiRecs = $parsed['recommendation'] ?? '';
                }
            }
        }

        $sql5 = "INSERT INTO co_ai_daily_briefings (briefing_date, total_tasks_today, critical_tasks, overdue_tasks, designer_overload_count, editor_overload_count, top_priorities, briefing_text, recommendations) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE total_tasks_today = VALUES(total_tasks_today), critical_tasks = VALUES(critical_tasks), overdue_tasks = VALUES(overdue_tasks), designer_overload_count = VALUES(designer_overload_count), editor_overload_count = VALUES(editor_overload_count), top_priorities = VALUES(top_priorities), briefing_text = VALUES(briefing_text), recommendations = VALUES(recommendations), generated_at = NOW()";
        $db->execute($sql5, [
            $today, $todayT, $crit, $over, $designerOL, $editorOL,
            $topsJson, $briefingText, $aiRecs,
        ]);

        NotificationService::sendWaGroup($briefingText);
        return true;
    }

    public static function answerChat(string $question, int $userId): string {
        $db = Database::getInstance();

        $sql1    = "SELECT COUNT(*) FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected')";
        $active  = $db->count($sql1, []);
        $sql2    = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $overdue = $db->count($sql2, []);
        $sla     = SlaService::getMonthlyStats();

        $sql3    = "SELECT u.name, k.creative_score FROM co_designer_kpi_monthly k JOIN co_users u ON k.user_id = u.id WHERE k.year_month = DATE_FORMAT(NOW(),'%Y-%m') ORDER BY k.creative_score DESC LIMIT 3";
        $top3    = $db->query($sql3, []);

        $sql4    = "SELECT u.name, w.workload_pct FROM co_workload_snapshots w JOIN co_users u ON w.user_id = u.id WHERE w.snapshot_date = CURDATE() AND w.workload_pct > 100 ORDER BY w.workload_pct DESC LIMIT 5";
        $ol      = $db->query($sql4, []);

        $context  = 'Data tim kreatif (per hari ini ' . date('d M Y') . '):' . PHP_EOL;
        $context .= '- Request aktif: ' . $active . PHP_EOL;
        $context .= '- Overdue: ' . $overdue . PHP_EOL;
        $context .= '- SLA achievement MTD: ' . $sla['achievement_pct'] . '%' . PHP_EOL;
        $context .= '- Avg turnaround: ' . $sla['avg_turnaround_h'] . ' jam' . PHP_EOL;
        if (!empty($top3)) {
            $context .= '- Top designer: ' . implode(', ', array_map(function($r) { return $r['name'] . ' (' . $r['creative_score'] . ')'; }, $top3)) . PHP_EOL;
        }
        if (!empty($ol)) {
            $context .= '- Overloaded: ' . implode(', ', array_column($ol, 'name')) . PHP_EOL;
        }

        $provider = self::getProvider();
        if ($provider instanceof DeepSeekProvider) {
            $reply = $provider->chat(self::$chatSystemPrompt, $context . PHP_EOL . 'Pertanyaan: ' . $question);
            if ($reply !== null) {
                return $reply;
            }
        }

        return 'API AI belum dikonfigurasi. Isi api_key di config/ai.php dan set provider=deepseek.';
    }

    public static function getCachedInsights(): array {
        $db  = Database::getInstance();
        $sql = "SELECT * FROM co_ai_insights WHERE (expires_at IS NULL OR expires_at > NOW()) ORDER BY generated_at DESC";
        return $db->query($sql, []);
    }

    public static function getTodayBriefing(): ?array {
        $db  = Database::getInstance();
        $sql = "SELECT * FROM co_ai_daily_briefings WHERE briefing_date = CURDATE() LIMIT 1";
        return $db->row($sql, []);
    }
}
