<?php
return [
    'api_key'    => getenv('AI_API_KEY')    ?: '',
    'model'      => getenv('AI_MODEL')      ?: 'deepseek-chat',
    'max_tokens' => (int)(getenv('AI_MAX_TOKENS') ?: 500),
    'timeout'    => (int)(getenv('AI_TIMEOUT')    ?: 30),
];
