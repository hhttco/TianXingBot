<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use App\Models\TgGroupConfig;

class Welcome extends Telegram {
    public $command = '/welcome';
    public $description = '设置欢迎词';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        // 权限验证
        if (!$this->common->power($message->chat_id, $message->user_id)) {
            abort(500, '请联系管理员操作');
        }

        if (!isset($message->args[0])) {
            abort(500, '参数有误！如需要关闭欢迎词请前往配置项配置');
        }

        // 例子：欢迎 {$username} 加入本群||按钮1&&地址||按钮2&&地址

        $str = ''; // 组装连接所有参数
        foreach ($message->args as $key => $value) {
            if ($str) {
                $str = $str . ' ' . $value;
            } else {
                $str = $value;
            }
        }

        // 保存到数据库
        $groupConfig = TgGroupConfig::where('group_id', $message->chat_id)->first();
        if (!$groupConfig) {
            abort(500, '请重新邀请本机器人入群');
        }

        $groupConfig->group_welcome = $str;
        $groupConfig->group_welcome_state = 1;
        $groupConfig->save();

        // 测试返回
        $this->common->welcome($str, $message->user_name, $message->chat_id);
    }
}
