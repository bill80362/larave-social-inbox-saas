<?php

namespace App\Actions\Reply\Drivers;

use App\Models\Channel;
use App\Models\Contact;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LineReplyDriver
{
    /**
     * @throws RuntimeException
     * @throws RequestException
     */
    public function send(Channel $channel, Contact $contact, string $content): ?string
    {
        $credentials = json_decode($channel->credentials, true);
        $token = $credentials['channel_access_token'] ?? null;

        if (empty($token)) {
            throw new RuntimeException('LINE channel_access_token is missing.');
        }

        $response = Http::withToken($token)
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $contact->platform_user_id,
                'messages' => [
                    ['type' => 'text', 'text' => $content],
                ],
            ]);

        $response->throw();

        return $response->json('sentMessages.0.id');
    }
}
