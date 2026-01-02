<?php

declare(strict_types=1);

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    'chat_id' => env('TELEGRAM_CHAT_ID', ''),

    'log_level' => env('TELEGRAM_LOG_LEVEL', 'critical'),

    'queue_name' => env('TELEGRAM_LOG_QUEUE', 'logs'),

    'log_title' => env('TELEGRAM_LOG_TITLE', 'Application Log'),

    'max_message_length' => (int) env('TELEGRAM_MAX_MESSAGE_LENGTH', 4000),

    'max_trace_lines' => (int) env('TELEGRAM_MAX_TRACE_LINES', 10),
];
