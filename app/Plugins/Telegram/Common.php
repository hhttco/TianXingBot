<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;

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
        // 判断群组权限
        $isAdmin = 0;
        $adminList = $this->telegramService->getChatAdministrators($chatId);
        foreach ($adminList->result as $key => $value) {
            if ($value->user->id == $userId) {
                $isAdmin = 1;
                break;
            }
        }

        return $isAdmin;
    }

    public function help($chatId) {
        $help = [
            '/help - 获取帮助信息',
            '/getme - 获取自己的信息',
            '/welcome - 设置欢迎词 (欢迎 {$username} 加入本群||按钮1&&地址||按钮2&&地址)',
        ];

        $text = implode(PHP_EOL, $help);
        $this->telegramService->sendMessage($chatId, $text, 'markdown');
    }

    // 发送入群验证
    public function checkJoin($chatId, $userId, $userName) {
        // 限制聊天 回答正确后解除限制
        $this->telegramService->restrictChatMember($chatId, $userId, time() + 90, false);

        $ques = $userName . " 你好呀！请回答一个问题，7+3等于多少？请在90秒内回答，否则会被我踢出群。";

        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "8", 'callback_data' => '/ckans-8'],
                    ['text' => "9", 'callback_data' => '/ckans-9'],
                    ['text' => "10", 'callback_data' => '/ckans-10'],
                    ['text' => "14", 'callback_data' => '/ckans-14']
                ]
            ]
        ]);

        $this->telegramService->sendMessageMarkup($chatId, $ques, $replyMarkup, 'markdown');
    }
}
