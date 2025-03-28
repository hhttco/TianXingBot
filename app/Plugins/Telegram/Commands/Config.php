<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Config extends Telegram {
    public $command = '/config';
    public $description = '设置管理';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        $this->common->editConfig($message->chat_id, $message->message_id, $message->user_id, 'config');
    }
}
