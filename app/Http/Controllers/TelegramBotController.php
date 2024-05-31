<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use JsonException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramBotController extends Controller
{

    private Api $telegram;

    private mixed $botToken;
    private string $apiUrl;

    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        $this->telegram = new Api(config('telegram.bots.mybot.token'));

        $this->botToken = env('BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";

    }

    /**
     *  use webhook method to get latest
     */
    public function webhook(Request $request)
    {
        $update = $request->all();
        if (!isset($update["callback_query"])) {
            $chat_id = $update["message"]['chat']['id'] ?? null;
            $text = $update["message"]['text'] ?? null;
            $username = $update["message"]['from']['username'] ?? null;
            $first_name = $update["message"]['from']['first_name'] ?? null;
            $last_name = $update["message"]['from']['last_name'] ?? null;
            $user_id = $update["message"]['from']['id'] ?? null;
            $message_id = $update["message"]['message_id'] ?? null;
            $forwarded_from_id = $update["message"]['forward_from']['id'] ?? null;

            if ($chat_id && $text) {
                $responseText = "Hello, $first_name! You said: $text";
                $this->bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => $responseText,
                ]);
            }
        }

        return response('OK',200);
    }

    private function bot($method, $parameters = []): void
    {
        $parameters["method"] = $method;
        $response = Http::post($this->apiUrl, $parameters);
        $response->json();
    }

    /**
     * use Poll method to get latest
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws JsonException
     */
    public function handleRequest($update): void
    {
        $chatId = $update['message']['chat']['id'] ?? null;
        $text = $update['message']['text'] ?? '';

        if ($text === '/latest_article') {
            $article = $this->getLatestArticle();
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $article
            ]);
        }
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function getLatestArticle():string
    {
        $client = new Client();
        $response = $client->get('URL_OF_YOUR_ARTICLES_API');

        $articles = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!empty($articles)){
            $latestArticle = $articles[0];

            return "{$latestArticle['title']}\n{$latestArticle['link']}";
        }
        return "No Article Found";
    }
}
