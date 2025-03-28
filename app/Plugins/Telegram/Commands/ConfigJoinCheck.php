<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class ConfigJoinCheck extends Telegram {
    public $command = '/configjoincheck';
    public $description = '关闭入群验证';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        $this->common->editConfig($message->chat_id, $message->message_id, $message->user_id, 'configjoincheck');
    }
}
