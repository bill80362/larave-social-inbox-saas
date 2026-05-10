<?php

namespace App\Actions\Reply\Drivers;

use App\Models\Channel;
use App\Models\Contact;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacebookReplyDriver
{
    /**
     * @throws RuntimeException
     * @throws RequestException
     */
    public function send(Channel $channel, Contact $contact, string $content): ?string
    {
        $credentials = json_decode($channel->credentials, true);
        $token = $credentials['page_access_token'] ?? null;

        if (empty($token)) {
            throw new RuntimeException('Facebook page_access_token is missing.');
        }

        $response = Http::post('https://graph.facebook.com/v19.0/me/messages', [
            'recipient' => ['id' => $contact->platform_user_id],
            'message' => ['text' => $content],
            'access_token' => $token,
        ]);

        $response->throw();

        return $response->json('message_id');
    }
}
