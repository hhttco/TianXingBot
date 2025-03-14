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
            $user->email = $msg->user_id . '@gmail.com';
            $user->name = $msg->user_name;
            $user->telegram_id = $msg->user_id;
            $user->password = $msg->user_id . '@gmail.com';

            $user->save();
        }

        $userInfo = [
            '系统ID: ' . $user->id,
            '用户ID: ' . $msg->user_id,
            '用户姓名: ' . $msg->user_name,
        ];

        $text = implode(PHP_EOL, $userInfo);
        $telegramService->sendMessage($message->chat_id, "当前用户信息：\n\n$text", 'markdown');
    }
}
