<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use App\Models\Users;

class GetMe extends Telegram {
    public $command = '/getme';
    public $description = '获取自己的信息';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        $user = Users::where('telegram_id', $message->user_id)->first();
        if (!$user) {
            // abort(500, '用户不存在');
            $user = new Users;
            $user->email = $message->user_id . '@gmail.com';
            $user->name = $message->user_name;
            $user->telegram_id = $message->user_id;
            $user->password = $message->user_id . '@gmail.com';

            $user->save();
        }

        $userInfo = [
            '系统ID: ' . $user->id,
            '用户ID: ' . $message->user_id,
            '用户姓名: ' . $message->user_name,
        ];

        $text = implode(PHP_EOL, $userInfo);
        $telegramService->sendMessage($message->chat_id, "当前用户信息：\n\n$text", 'markdown');
    }
}
