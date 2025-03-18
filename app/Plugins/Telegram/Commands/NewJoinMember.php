<?php

namespace App\Plugins\Telegram\Commands;

use App\Services\TelegramService;
use App\Models\TgGroup;

class NewJoinMember {
    protected $telegramService;
    protected $thisBotId;

    public function __construct()
    {
        $this->telegramService = new TelegramService();
        $this->thisBotId = explode(':', config('telegram.bot.token'))[0];
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
            $retText = "[$userName](tg://user?id=$newMemberId) 请将本机器人设置为管理员";

            // 如果是被邀请进入判断数据库是否存在
            $this->group($data);
        } else {
            $retText = "欢迎新用户 [$userName](tg://user?id=$newMemberId)";
        }

        $this->telegramService->sendMessage($chatId, $retText, 'markdown');
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
    }
}
