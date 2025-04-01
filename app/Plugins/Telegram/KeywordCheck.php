<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;
use App\Models\TgGroupKeyword;
use Illuminate\Support\Facades\Log;

class KeywordCheck {
    protected $telegramService;

    public function __construct()
    {
        $this->telegramService = new TelegramService();
    }

    public function handle($data) {
        if ($data['message']['chat']['type'] === 'private') return;
        $chatId = $data['message']['chat']['id'];
        $messageId = $data['message']['message_id'];
        $messageText = $data['message']['text'];

        // 获取群聊的所有关键词
        $keywords = TgGroupKeyword::where('group_id', $chatId)->where('group_keyword_state', 1)->get();
        foreach ($keywords as $k => $v) {
            if (strpos($messageText, $v->group_keyword) !== false) {
                $this->telegramService->sendMessage($chatId, $v->group_keyword_reply, 'markdown', $messageId);
                break;
            }
        }
    }
}
