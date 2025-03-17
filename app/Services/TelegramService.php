<?php
namespace App\Services;

use \Curl\Curl;

class TelegramService {
    protected $api;

    public function __construct($token = '')
    {
        $this->api = 'https://api.telegram.org/bot' . config('telegram.bot.token', $token) . '/';
    }

    public function setWebhook(string $url)
    {
        return $this->request('setWebhook', [
            'url' => $url
        ]);
    }

    public function getMe()
    {
        return $this->request('getMe');
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = '', int $replyToMessageId = NULL)
    {
        $this->request('sendMessage', [
            'chat_id'             => $chatId,
            'text'                => $text,
            'parse_mode'          => $parseMode,
            'reply_to_message_id' => $replyToMessageId
        ]);
    }

    public function sendMessageMarkup(int $chatId, string $text, string $reply_markup, string $parseMode = '')
    {
        // 可支持 markdown 语法
        $this->request('sendMessage', [
            'chat_id'      => $chatId,
            'text'         => $text,
            'reply_markup' => $reply_markup,
            'parse_mode'   => $parseMode
        ]);
    }

    public function editMessageMarkup(int $chatId, int $messageId, string $text, string $reply_markup, string $parseMode = '')
    {
        // 可支持 markdown 语法
        $this->request('editMessageText', [
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'         => $text,
            'reply_markup' => $reply_markup,
            'parse_mode'   => $parseMode
        ]);
    }

    public function deleteMessage(int $chatId, int $messageId)
    {
        $this->request('deleteMessage', [
            'chat_id'    => $chatId,
            'message_id' => $messageId
        ]);
    }

    public function sendPhoto(int $chatId, string $photoUrl)
    {
        $this->request('sendPhoto', [
            'chat_id'             => $chatId,
            'photo'               => $photoUrl
        ]);
    }

    // 封禁成员
    public function banChatMember(int $chatId, int $userId, int $untilDate = 1)
    {
        $this->request('banChatMember', [
            'chat_id'          => $chatId,
            'user_id'          => $userId,
            'until_date'       => $untilDate, // 366 天或从当前时间算起不到 30 秒，则视为永久禁言
            'revoke_messages'  => true
        ]);
    }

    // 解禁成员
    public function unbanChatMember(int $chatId, int $userId)
    {
        $this->request('unbanChatMember', [
            'chat_id'  => $chatId,
            'user_id'  => $userId
        ]);
    }

    // 限制成员聊天
    public function restrictChatMember(int $chatId, int $userId, int $untilDate = 1)
    {
        $this->request('restrictChatMember', [
            'chat_id'  => $chatId,
            'user_id'  => $userId,
            // 'permissions' => [ // 使用这个每个权限都要单独设置
            //     'can_send_messages' => false // 可以发送消息
            // ],
            'use_independent_chat_permissions' => false,
            'until_date'  => $untilDate
        ]);
    }

    private function request(string $method, array $params = [])
    {
        $curl = new Curl();
        $curl->get($this->api . $method . '?' . http_build_query($params));
        $response = $curl->response;
        $curl->close();

        if (!isset($response->ok)) abort(500, '请求失败');
        if (!$response->ok) {
            // 判断是否是权限不足
            if (isset($params['chat_id']) && strpos($response->description, 'not enough rights') !== false) {
                $this->sendMessage($params['chat_id'], '天星机器人权限不足！请将机器人设置为群管理员', 'markdown');
            }

            abort(500, '来自TG的错误：' . $response->description);
        }

        return $response;
    }
}
