<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;
use App\Models\TgGroupKeyword;
use App\Models\TgGroupConfig;
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

        // 检查消息中文本
        $this->check($data);

        // 获取群聊的所有关键词
        $keywords = TgGroupKeyword::where('group_id', $chatId)->where('group_keyword_state', 1)->get();
        foreach ($keywords as $k => $v) {
            if (strpos($messageText, $v->group_keyword) !== false) {
                $this->telegramService->sendMessage($chatId, $v->group_keyword_reply, 'markdown', $messageId);
                break;
            }
        }
    }

    public function check($data) {
        // 判断是否有配置
        $groupConfig = TgGroupConfig::where('group_id', $data['message']['chat']['id'])->first();
        if (!$groupConfig) {
            abort(500, '请重新邀请机器人入群');
        }

        if ($groupConfig->group_can_forward == 1) {
            $this->delForwardMsg($data);
        }
    }

    public function delForwardMsg($data) {
        // 删除转发消息
        if (isset($data['message']['forward_origin']) ||
            isset($data['message']['forward_from_chat']) ||
            isset($data['message']['forward_from_message_id']) ||
            isset($data['message']['forward_date'])) {
            $this->telegramService->deleteMessage($data['message']['chat']['id'], $data['message']['message_id']);
        }
    }
}
