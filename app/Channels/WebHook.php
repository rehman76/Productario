<?php


namespace App\Channels;


use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class WebHook
{
    protected  $url;

    public function  __construct()
    {
        $this->url = env('WEBHOOK_URL');
    }

    public function send($notifiable, Notification $notification)
    {

        try {
            $response = Http::post($this->url, ['data' => $notification->toWebHook($notifiable)])
                ->throw();

            info('webhook response', [
                'response' => $response->json()
            ]);
        } catch (\Throwable $th) {
            info('failed', [
                'error' => $th->getMessage()
            ]);
        }
    }
}
