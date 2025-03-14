<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $msg;
    protected $telegramService;
    protected $thisBotId;

    public function __construct(Request $request)
    {
        if ($request->input('access_token') !== md5(config('telegram.bot.token'))) {
            abort(401);
        }

        $this->telegramService = new TelegramService();
        $this->thisBotId = '7309768988';
    }

    public function webhook(Request $request)
    {
        $this->formatMessage($request->input());
        $this->newJoinMember($request->input());
        $this->handle();
        return;
    }

    private function formatMessage(array $data)
    {
        Log::info(json_encode($data));

        // if (!isset($data['message'])) return;
        if (!isset($data['message']['text']) && !isset($data['callback_query'])) return;

        $obj = new \StdClass();

        // 如果是键盘回复 可以封装一下
        if (isset($data['callback_query'])) {
            $obj->command =$data['callback_query']['data'];
            $obj->callback_query_id =$data['callback_query']['id'];
            $obj->chat_id = $data['callback_query']['message']['chat']['id'];
            $obj->user_id = $data['callback_query']['from']['id'];

            if (isset($data['callback_query']['from']['first_name'])) {
                $firstName = $data['callback_query']['from']['first_name'];
                $obj->user_name = $firstName;
                if (isset($data['callback_query']['from']['last_name'])) {
                    $obj->user_name = $firstName . " " . $data['callback_query']['from']['last_name'];
                }
            } else {
                if (isset($data['callback_query']['from']['username'])) {
                    $obj->user_name = $data['callback_query']['from']['username'];
                } else {
                    $obj->user_name = $data['callback_query']['from']['id'];
                }
            }

            $obj->message_id = $data['callback_query']['message']['message_id'];
            $obj->text = $data['callback_query']['message']['text'];
            $obj->message_type = 'message';
            $obj->is_private = $data['callback_query']['message']['chat']['type'] === 'private' ? true : false;

            $this->msg = $obj;
            // Log::info("这是键盘回复：" . json_encode($obj));
        } else {
            $text = explode(' ', $data['message']['text']);
            $obj->command = $text[0];
            $obj->args = array_slice($text, 1);
            $obj->chat_id = $data['message']['chat']['id'];

            if (isset($data['message']['chat']['title'])) {
                $obj->chat_name = $data['message']['chat']['title'];
            }
        
            $obj->message_id = $data['message']['message_id'];
            $obj->message_type = 'message';
            $obj->text = $data['message']['text'];
            $obj->is_private = $data['message']['chat']['type'] === 'private';

            $obj->user_id = $data['message']['from']['id'];
            if (isset($data['message']['from']['first_name'])) {
                $firstName = $data['message']['from']['first_name'];
                $obj->user_name = $firstName;
                if (isset($data['message']['from']['last_name'])) {
                    $obj->user_name = $firstName . " " . $data['message']['from']['last_name'];
                }
            } else {
                if (isset($data['message']['from']['username'])) {
                    $obj->user_name = $data['message']['from']['username'];
                } else {
                    $obj->user_name = $data['message']['from']['id'];
                }
            }

            if (isset($data['message']['reply_to_message']['text'])) {
                $obj->message_type = 'reply_message';
                $obj->reply_text = $data['message']['reply_to_message']['text'];
            }

            $this->msg = $obj;
        }
    }

    public function handle()
    {
        if (!$this->msg) return;
        $msg = $this->msg;
        $commandName = explode('@', $msg->command);

        // To reduce request, only commands contains @ will get the bot name
        if (count($commandName) == 2) {
            $botName = $this->getBotName();
            if ($commandName[1] === $botName){
                $msg->command = $commandName[0];
            }
        }

        try {
            foreach (glob(base_path('app//Plugins//Telegram//Commands') . '/*.php') as $file) {
                $command = basename($file, '.php');
                $class = '\\App\\Plugins\\Telegram\\Commands\\' . $command;
                if (!class_exists($class)) continue;
                $instance = new $class();
                if ($msg->message_type === 'message') {
                    if (!isset($instance->command)) continue;
                    if ($msg->command !== $instance->command) continue;
                    $instance->handle($msg);
                    return;
                }

                if ($msg->message_type === 'reply_message') {
                    if (!isset($instance->regex)) continue;
                    if (!preg_match($instance->regex, $msg->reply_text, $match)) continue;
                    $instance->handle($msg, $match);
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->telegramService->sendMessage($msg->chat_id, $e->getMessage());
        }
    }

    public function newJoinMember(array $data)
    {
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
        } else {
            $retText = "欢迎新用户 [$userName](tg://user?id=$newMemberId)";
        }

        $this->telegramService->sendMessage($chatId, $retText, 'markdown');
    }
}
