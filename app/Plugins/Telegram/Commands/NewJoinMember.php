<?php

namespace App\Plugins\Telegram\Commands;

use App\Services\TelegramService;

class NewJoinMember {
    public function handle($data) {
        // 如果不是新加入
        if (!isset($data['message']['new_chat_participant'])) return;

        $chatId = $data['message']['chat']['id'];
        $newMemberId = $data['message']['new_chat_participant']['id'];

        // 获取用户姓名
        $userName = $data['message']['new_chat_participant']['first_name'];
        if (isset($data['message']['new_chat_participant']['last_name'])) {
            $userName = $userName . " " . $data['message']['new_chat_participant']['last_name'];
        }

        if ($newMemberId == explode(':', config('telegram.bot.token'))[0]) {
            $retText = "[$userName](tg://user?id=$newMemberId) 请将本机器人设置为管理员";
        } else {
            $retText = "欢迎新用户 [$userName](tg://user?id=$newMemberId)";
        }

        $telegramService = new TelegramService();
        $telegramService->sendMessage($chatId, $retText, 'markdown');
    }
}
