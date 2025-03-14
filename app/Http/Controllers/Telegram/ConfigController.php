<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;

class ConfigController extends Controller
{
    // setWebhook
    public function setWebhook()
    {
        $hookUrl = url('/telegram/webhook?access_token=' . md5(config('telegram.bot.token')));
        $telegramService = new TelegramService();
        $telegramService->setWebhook($hookUrl);

        if (!empty(config('telegram.bot.admin_id'))) {
            $telegramService->sendMessage(config('telegram.bot.admin_id'), "机器人启动成功!🎉🎁😄", 'markdown');
        }

        return response([
            'data' => true
        ]);
    }
}
