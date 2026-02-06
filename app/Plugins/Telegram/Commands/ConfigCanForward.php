<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class ConfigCanForward extends Telegram {
    public $command = '/configcanforward';
    public $description = '配置是否删除转入群的消息';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        $this->common->editConfig($message->chat_id, $message->message_id, $message->user_id, 'configcanforward');
    }
}
