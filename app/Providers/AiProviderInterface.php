<?php
interface AiProviderInterface {
    public function chat(string $systemPrompt, string $userMessage): ?string;
}
