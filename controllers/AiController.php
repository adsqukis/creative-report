<?php
class AiController {
    public function insights(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db       = Database::getInstance();
        $insights = AiCreativeAnalyst::getCachedInsights();

        $byType   = [];
        foreach ($insights as $ins) {
            $type = $ins['insight_type'];
            if (!isset($byType[$type])) {
                $byType[$type] = $ins;
            }
        }

        $sql1     = "SELECT COUNT(*) FROM co_ai_insights WHERE expires_at > NOW()";
        $liveCount = $db->count($sql1, []);

        $pageTitle = 'AI Insights';
        $view      = 'ai/insights';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function briefing(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $briefing  = AiCreativeAnalyst::getTodayBriefing();
        $history   = Database::getInstance()->query(
            "SELECT * FROM co_ai_daily_briefings ORDER BY briefing_date DESC LIMIT 7",
            []
        );
        $pageTitle = 'Daily Briefing';
        $view      = 'ai/briefing';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function chat(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db       = Database::getInstance();
        $uid      = Auth::id();
        $today    = date('Y-m-d');

        $cfg      = require APP_ROOT . '/config/ai.php';
        $limit    = (int)($cfg['daily_chat_limit'] ?? 20);
        $sql1     = "SELECT request_count FROM co_ai_chat_ratelimit WHERE user_id = ? AND request_date = ? LIMIT 1";
        $rl       = $db->row($sql1, [$uid, $today]);
        $usedToday = $rl ? (int)$rl['request_count'] : 0;
        $remaining = max(0, $limit - $usedToday);

        $sessionId = $_COOKIE['co_chat_session'] ?? bin2hex(random_bytes(16));
        setcookie('co_chat_session', $sessionId, time() + 86400 * 7, '/');

        $sql2    = "SELECT role, message, created_at FROM co_ai_chat_history WHERE session_id = ? ORDER BY created_at DESC LIMIT 20";
        $history = array_reverse($db->query($sql2, [$sessionId]));

        $pageTitle = 'AI Chat';
        $view      = 'ai/chat';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function chatSend(): void {
        header('Content-Type: application/json');

        if (empty($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $uid       = (int)$_SESSION['user_id'];
            $db        = Database::getInstance();
            $today     = date('Y-m-d');
            $cfg       = require APP_ROOT . '/config/ai.php';
            $limit     = (int)($cfg['daily_chat_limit'] ?? 20);
            $sessionId = trim($_POST['session_id'] ?? '');
            $question  = trim($_POST['message'] ?? '');

            if ($question === '') {
                echo json_encode(['error' => 'Pesan kosong']);
                exit;
            }

            $sql1 = "SELECT request_count FROM co_ai_chat_ratelimit WHERE user_id = ? AND request_date = ? LIMIT 1";
            $rl   = $db->row($sql1, [$uid, $today]);
            if ($rl && (int)$rl['request_count'] >= $limit) {
                echo json_encode(['error' => 'Limit ' . $limit . ' pertanyaan per hari sudah tercapai.']);
                exit;
            }

            $reply = AiCreativeAnalyst::answerChat($question, $uid);

            if ($sessionId !== '') {
                $sql2 = "INSERT INTO co_ai_chat_history (session_id, user_id, role, message) VALUES (?, ?, 'user', ?)";
                $db->execute($sql2, [$sessionId, $uid, $question]);
                $sql3 = "INSERT INTO co_ai_chat_history (session_id, user_id, role, message) VALUES (?, ?, 'assistant', ?)";
                $db->execute($sql3, [$sessionId, $uid, $reply]);
            }

            if (!$rl) {
                $sql4 = "INSERT INTO co_ai_chat_ratelimit (user_id, request_date, request_count) VALUES (?, ?, 1)";
                $db->execute($sql4, [$uid, $today]);
            } else {
                $sql4 = "UPDATE co_ai_chat_ratelimit SET request_count = request_count + 1 WHERE user_id = ? AND request_date = ?";
                $db->execute($sql4, [$uid, $today]);
            }

            echo json_encode(['reply' => $reply]);
        } catch (Throwable $e) {
            error_log('[AiController::chatSend] ' . $e->getMessage());
            echo json_encode(['error' => 'Terjadi kesalahan server. Coba lagi.']);
        }
        exit;
    }

    public function reports(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db      = Database::getInstance();
        $sql     = "SELECT * FROM co_ai_reports ORDER BY generated_at DESC LIMIT 10";
        $reports = $db->query($sql, []);

        $pageTitle = 'AI Reports';
        $view      = 'ai/reports';
        require APP_ROOT . '/views/layouts/app.php';
    }
}
