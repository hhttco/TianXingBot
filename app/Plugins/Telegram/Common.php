<?php

namespace App\Plugins\Telegram;

use App\Services\TelegramService;
use Illuminate\Support\Facades\Redis;
use App\Models\TgGroupConfig;
use App\Jobs\TelegramDeleteMessage;

class Common {
    protected $telegramService;
    protected $thisBotId;

    // 通用函数方法
    public function __construct()
    {
        $this->telegramService = new TelegramService();
        $this->thisBotId = explode(':', config('telegram.bot.token'))[0];
    }

    public function welcome($chatId, $userId, $userName) {
        $groupConfig = TgGroupConfig::where('group_id', $chatId)->first();
        if (!$groupConfig) {
            abort(500, '请重新邀请本机器人入群');
        }

        if ($groupConfig->group_welcome_state != 1 || !$groupConfig->group_welcome) {
            $retText = "欢迎新用户 [$userName](tg://user?id=$userId)";
            $response = $this->telegramService->sendMessage($chatId, $retText, 'markdown');
        } else {
            // 替换名称 单引号 不支持变量解析 双引号支持变量解析
            $str = str_replace('{$username}', "[$userName](tg://user?id=$userId)", $groupConfig->group_welcome);

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

                $response = $this->telegramService->sendMessageMarkup($chatId, $arr[0], $replyMarkup, 'markdown');
            } else {
                // 没有按钮
                $response = $this->telegramService->sendMessage($chatId, $str, 'markdown');
            }
        }

        if ($response->result->message_id) {
            $responseMessageId = $response->result->message_id;

            // 放入延迟消息队列中
            // TelegramDeleteMessage::dispatch([
            //     'id'  => '123666666',
            //     'id2' => 'rfd9999'
            // ])->delay(now()->addMinutes(1));
            TelegramDeleteMessage::dispatch([
                'chat_id'    => $chatId,
                'message_id' => $response->result->message_id
            ]);
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
            '/config - 获取设置信息',
            '/getme - 获取自己的信息',
            '/welcome - 设置欢迎词 (/welcome 欢迎 {$username} 加入本群||按钮1&&地址||按钮2&&地址)',
            '/add - 添加关键词 (/add 优惠码===abc111)',
            '/adddel - 删除关键词 (/adddel 优惠码)'
        ];

        $text = implode(PHP_EOL, $help);
        $this->telegramService->sendMessage($chatId, $text, 'markdown');
    }

    // 发送入群验证
    public function checkJoin($chatId, $userId, $userName) {
        // 限制聊天 回答正确后解除限制
        $this->telegramService->restrictChatMember($chatId, $userId, time() + 1, false);

        $firstNum  = rand(1, 9);
        $secondNum = rand(1, 9);
        $ques = $userName . ' 你好呀！请回答一个问题，' . $firstNum . ' + ' . $secondNum . ' 等于多少？请在90秒内回答，否则会被我踢出群。';
        $replyMarkup = $this->getCheckJoinStr($chatId, $userId, $firstNum, $secondNum);

        $this->telegramService->sendMessageMarkup($chatId, $ques, $replyMarkup, 'markdown');
    }

    // 获取入群验证发送键盘的字符串
    public function getCheckJoinStr($chatId, $userId, $firstNum, $secondNum) {
        $ansNum = $firstNum + $secondNum;

        $itemArr = array();
        $itemArr[] = ['text' => $ansNum, 'callback_data' => '/ckans-' . $ansNum];
        for ($i = 0; $i < 3; $i++) { 
            $iNum = rand(1, 20);
            if ($iNum == $ansNum) {
                $i--;
                continue;
            }

            $arrItme = ['text' => $iNum, 'callback_data' => '/ckans-' . $iNum];
            $itemArr[] = $arrItme;
        }

        // 重新排列顺序
        shuffle($itemArr);

        $ret = [
            'inline_keyboard' => [
                $itemArr
            ]
        ];

        // 把正确答案存到 redis
        Redis::setex('checkJoin' . $chatId . $userId, 8640, $ansNum);

        $replyMarkup = json_encode($ret);

        return $replyMarkup;
    }

    // 修改配置
    public function editConfig($chatId, $messageId, $userId, $type) {
        // 权限验证
        if (!$this->power($chatId, $userId)) {
            abort(500, '请联系管理员操作');
        }

        // 获取配置列表
        $groupConfig = TgGroupConfig::where('group_id', $chatId)->first();
        if (!$groupConfig) {
            abort(500, '请重新邀请本机器人入群');
        }

        // 修改数据
        if ($type === 'configwelcome') {
            $groupConfig->group_welcome_state = $groupConfig->group_welcome_state ^ 1;
        }

        if ($type === 'configjoincheck') {
            $groupConfig->group_join_check = $groupConfig->group_join_check ^ 1;
        }

        $sendText = '感谢使用本机器人！使用前请将本机器人设置为管理员';
        $replyMarkup = $this->getChannelConfig($groupConfig);

        // 如果是获取配置
        if ($type === 'config') {
            $this->telegramService->sendMessageMarkup($chatId, $sendText, $replyMarkup, 'markdown');
        } else {
            $groupConfig->save();
            $this->telegramService->editMessageMarkup($chatId, $messageId, $sendText, $replyMarkup, 'markdown');
        }
    }

    public function getChannelConfig($groupConfig) {
        // 发送消息
        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '获取配置列表', 'callback_data' => '/config']
                ],
                [
                    ['text' => $groupConfig->group_welcome_state ? '✅ 关闭欢迎词' : '❌ 开启欢迎词', 'callback_data' => '/configwelcome']
                ],
                [
                    ['text' => $groupConfig->group_join_check ? '✅ 关闭入群验证' : '❌ 开启入群验证', 'callback_data' => '/configjoincheck']
                ],
                [
                    ['text' => '关闭配置菜单', 'callback_data' => '/configclose']
                ]
            ]
        ]);

        return $replyMarkup;
    }
}
