<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;
use App\Models\TgGroup;
use App\Models\TgGroupConfig;

class NewJoinMember {
    protected $telegramService;
    protected $thisBotId;
    protected $common;

    public function __construct()
    {
        $this->telegramService = new TelegramService();
        $this->thisBotId = explode(':', config('telegram.bot.token'))[0];
        $this->common = new Common();
    }

    public function handle($data) {
        // 如果是离开群组
        if (isset($data['message']['left_chat_participant'])) {
            $this->left($data);
            return;
        }

        // 如果不是新加入
        if (!isset($data['message']['new_chat_participant'])) return;

        $chatId = $data['message']['chat']['id'];
        $newMemberId = $data['message']['new_chat_participant']['id'];

        // 获取用户姓名
        $userName = $data['message']['new_chat_participant']['first_name'];
        if (isset($data['message']['new_chat_participant']['last_name'])) {
            $userName = $userName . " " . $data['message']['new_chat_participant']['last_name'];
        }

        if ($newMemberId == $this->thisBotId) {
            // 如果是被邀请进入判断数据库是否存在
            $this->group($data);
        } else {
            // 判断是否有配置
            $groupConfig = TgGroupConfig::where('group_id', $chatId)->first();
            if (!$groupConfig) {
                abort(500, '请重新邀请机器人入群');
            }

            // 判断是否开启了入群验证
            if ($groupConfig->group_join_check == 1) {
                // 如果是创建者禁言函数无法返回结果
                if (!$this->common->power($chatId, $newMemberId)) {
                    $this->common->checkJoin($chatId, $newMemberId, $userName);
                    return;
                }
            }

            // 开启了欢迎词并且存在欢迎词
            if ($groupConfig->group_welcome_state == 1 && $groupConfig->group_welcome) {
                $this->common->welcome($chatId, $newMemberId, $userName, $groupConfig->group_welcome);
                return;
            }

            $retText = "欢迎新用户 [$userName](tg://user?id=$newMemberId)";
            $this->telegramService->sendMessage($chatId, $retText, 'markdown');
        }
    }

    public function left($data) {
        $groupId = $data['message']['chat']['id'];
        $leftMemberId = $data['message']['left_chat_participant']['id'];
        if ($leftMemberId == $this->thisBotId) {
            // 更新数据库状态
            $group = TgGroup::where('group_id', $groupId)->first();
            if ($group) {
                $group->group_bot_state = 0;
                $group->save();
            }
        }
    }

    public function group($data) {
        if (!isset($data['message'])) return;

        $data = $data['message'];

        $groupId = $data['chat']['id'];

        // 获取创建人姓名
        $userName = $data['from']['first_name'];
        if (isset($data['from']['last_name'])) {
            $userName = $userName . " " . $data['from']['last_name'];
        }

        // 判断群组是否在数据库
        $group = TgGroup::where('group_id', $groupId)->first();
        if (!$group) {
            $group = new TgGroup;
            $group->group_id = $groupId;
        }

        // 当前群组人数
        $n = $this->telegramService->getChatMemberCount($groupId);

        $group->group_name = $data['chat']['title'];
        $group->group_user_num = $n->result;
        $group->create_user_id = $data['from']['id'];
        $group->create_user_name = $userName;
        $group->group_bot_state = 1;

        $group->save();

        // 群组配置表
        $groupConfig = TgGroupConfig::where('group_id', $groupId)->first();
        if (!$groupConfig) {
            $groupConfig = new TgGroupConfig;
            $groupConfig->group_id = $groupId;
            $groupConfig->save();
        }

        // 发送配置欢迎
        $sendText = '感谢使用本机器人！使用前请将本机器人设置为管理员';
        $replyMarkup = $this->common->getChannelConfig($groupConfig);
        $this->telegramService->sendMessageMarkup($groupId, $sendText, $replyMarkup, 'markdown');
    }
}
