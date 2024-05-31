<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;

class SendScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-scheduled-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled messages to Telegram';

    /**
     * Execute the console command.
     * @throws JsonException
     */
    public function handle(): void
    {
        $article_id = json_decode(Storage::get('id.txt'), true, 512, JSON_THROW_ON_ERROR);
        $article_id++;
        $response = Http::get("YOUR_URL", ['id' => $article_id]);
        $json_answer = $response->json();

        if (isset($json_answer["article"])) {
            $article_title = $json_answer["article"]["title"];
            $article_link = "example.com/articles/".$json_answer["article"]["slug"];
            $article_description = $json_answer["article"]["description"];
            $message = "<b>$article_title</b>\n\n🔺 $article_description\n\n🌐 : <a href='$article_link'>$article_title</a>";

            $photo = "https://example.com".$json_answer["article"]['poster']."?time=".time();

            $chatIds = ['141872429', '1861614905', '@Channel_ID'];
            foreach ($chatIds as $chatId) {
                SendTelegramMessage::dispatch($chatId, $message);
            }

            Storage::put('id.txt', json_encode($article_id, JSON_THROW_ON_ERROR));
        } else {
            $this->info("Article not found");
        }
    }
}
