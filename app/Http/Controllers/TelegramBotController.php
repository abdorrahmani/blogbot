<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramBotController extends Controller
{

    private Api $telegram;

    /**
     * @throws TelegramSDKException
     */
    public function __construct()
    {
        $this->telegram = new Api(config('telegram.bots.mybot.token'));

    }

    /**
     * @throws TelegramSDKException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function webhook()
    {
        $update = $this->telegram->getWebhookUpdate();

        $chatId = $update['message']['chat_id'] ?? null;
        $text = $update['message'] ['text'] ?? null;

        if ($text === "/latest_article"){
            $article = $this->getLatestArticle();

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $article
            ]);
        }

        return response('OK',200);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function getLatestArticle():string
    {
        $client = new Client();
        $response = $client->get('YOUR_URL');

        $articles = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!empty($articles)){
            $latestArticle = $articles[0];

            return "{$latestArticle['title']}\n{$latestArticle['link']}";
        }
        return "No Article Found";
    }
}
