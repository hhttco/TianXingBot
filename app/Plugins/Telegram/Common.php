<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;
use App\Models\TgGroup;

class Common {
    protected $telegramService;
    protected $thisBotId;

    // 通用函数方法
    public function __construct()
    {
        $this->telegramService = new TelegramService();
        $this->thisBotId = explode(':', config('telegram.bot.token'))[0];
    }

    public function welcome($str, $userName, $chatId) {
        // 替换名称
        $str = str_replace('{$username}', $userName, $str);

        // 设置按钮 explode
        $arr = explode('||', $str);
        if (count($arr) > 1) {
            // 有按钮
            $replyMarkupItem = array();
            foreach ($arr as $key => $value) {
                if ($key == 0) continue;
                $bArr = explode('&&', $value);
                $replyMarkupItem[] = ['text' => $bArr[0], 'callback_data' => $bArr[1]];
            }

            $replyMarkup = json_encode([
                'inline_keyboard' => [$replyMarkupItem]
            ]);

            $this->telegramService->sendMessageMarkup($chatId, $arr[0], $replyMarkup, 'markdown');
        } else {
            // 没有按钮
            $this->telegramService->sendMessage($chatId, $str, 'markdown');
        }
    }

    public function power($chatId, $userId) {
        // 判断群组
        $group = TgGroup::where('group_id', $chatId)->first();
        if ($group && $group->create_user_id != $userId) {
            abort(500, '请联系管理员操作');
        }
    }
}
