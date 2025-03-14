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

    private function request(string $method, array $params = [])
    {
        $curl = new Curl();
        $curl->get($this->api . $method . '?' . http_build_query($params));
        $response = $curl->response;
        $curl->close();

        if (!isset($response->ok)) abort(500, '请求失败');
        if (!$response->ok) {
            abort(500, '来自TG的错误：' . $response->description);
        }

        return $response;
    }
}
