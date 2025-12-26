<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram Bot API token. You can get this from @BotFather on Telegram.
    | Create a new bot with /newbot command and copy the token.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Telegram Chat ID
    |--------------------------------------------------------------------------
    |
    | The chat ID where log messages will be sent.
    | This can be a user ID, group ID, or channel ID.
    |
    | To get your chat ID:
    | 1. Start a chat with your bot
    | 2. Send any message to the bot
    | 3. Visit: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
    | 4. Look for "chat":{"id": YOUR_CHAT_ID}
    |
    | For groups/channels, the ID is usually negative (e.g., -1001234567890)
    |
    */
    'chat_id' => env('TELEGRAM_CHAT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configure which log levels should be sent to Telegram.
    | Available levels: debug, info, notice, warning, error, critical, alert, emergency
    |
    */
    'log_level' => env('TELEGRAM_LOG_LEVEL', 'critical'),
];
