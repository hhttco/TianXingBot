<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\TelegramService;
// use Illuminate\Support\Facades\Log;

class TelegramDeleteMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params, $queue = 'telegram_delete_message')
    {
        //
        $this->onQueue($queue);
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        // Log::info("====>>>>> 队列消息" . json_encode($this->params));
        $telegramService = new TelegramService();
        $telegramService->deleteMessage($this->params['chat_id'], $this->params['message_id']);
    }
}
