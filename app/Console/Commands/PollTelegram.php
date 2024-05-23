<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramBotController;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class PollTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram for new messages';

    private Api $telegram;
    private TelegramBotController $controller;

    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        parent::__construct();
        $this->telegram = new Api(config('telegram.bots.mybot.token'));
        $this->controller = new TelegramBotController();
    }


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $offset = 0;
        $requestCount = 0;

        while (true) {
            try {
                $updates = $this->telegram->getUpdates(['offset' => $offset, 'timeout' => 10]);
                $requestCount++;
                $this->info("Request Count: $requestCount | Time: " . now()->toDateTimeString());
                foreach ($updates as $update) {
                    $offset = $update['update_id'] + 1;
                    $this->info("Update: " . json_encode($update, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
                    $this->controller->handleRequest($update);

                    Log::info('Handled update', ['update' => $update]);
                }

                sleep(1); // Sleep for a second to avoid hitting rate limits
            } catch (\Exception $e) {
                $this->error('Error while polling Telegram: ' . $e->getMessage());
                Log::error('Error while polling Telegram', ['exception' => $e]);
            } catch (GuzzleException $e) {
                $this->error('Error in handleRequest: ' . $e->getMessage());
                Log::error('Error in handleRequest', ['exception' => $e]);
            }
        }
    }
}
