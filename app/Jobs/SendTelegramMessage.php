<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;
    protected string $message;
    /**
     * Create a new job instance.
     */
    public function __construct(string $chatId, string $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $botToken = env('BOT_TOKEN');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $parameters = [
            'chat_id' => $this->chatId,
            'text' => $this->message,
            'parse_mode' => 'HTML',
        ];

        Http::post($apiUrl, $parameters);
    }
}
