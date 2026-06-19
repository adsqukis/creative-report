<?php
class DeepSeekProvider implements AiProviderInterface {
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private int    $timeout;

    public function __construct() {
        $cfg             = require APP_ROOT . '/config/ai.php';
        $this->apiKey    = $cfg['api_key']    ?? '';
        $this->model     = $cfg['model']      ?? 'deepseek-chat';
        $this->maxTokens = (int)($cfg['max_tokens'] ?? 500);
        $this->timeout   = (int)($cfg['timeout']    ?? 30); // naikkan default timeout
    }

    public function chat(string $systemPrompt, string $userMessage): ?string {
        if (empty($this->apiKey) || $this->apiKey === 'sk-your-deepseek-api-key-here') {
            return null;
        }

        $payload = json_encode([
            'model'       => $this->model,
            'max_tokens'  => $this->maxTokens,
            'temperature' => 0.3,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userMessage],
            ],
        ]);

        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt($ch, CURLOPT_POST,            1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1);
        curl_setopt($ch, CURLOPT_TIMEOUT,         $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false); // shared hosting kadang SSL bermasalah
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $raw     = curl_exec($ch);
        $curlErr = curl_errno($ch);
        $curlMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log error untuk debugging (cek di error_log server)
        if ($curlErr || empty($raw)) {
            error_log('[DeepSeek] cURL error #' . $curlErr . ': ' . $curlMsg);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('[DeepSeek] HTTP ' . $httpCode . ' — ' . substr($raw, 0, 300));
            return null;
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[DeepSeek] JSON decode error: ' . json_last_error_msg());
            return null;
        }

        return $data['choices'][0]['message']['content'] ?? null;
    }
}
