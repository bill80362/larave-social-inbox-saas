<?php

namespace App\Actions\Reply\Drivers;

use App\Models\Channel;
use App\Models\Contact;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleBusinessReplyDriver
{
    /**
     * @throws RuntimeException
     * @throws RequestException
     */
    public function send(Channel $channel, Contact $contact, string $content): ?string
    {
        $credentials = json_decode($channel->credentials, true);
        $token = $credentials['access_token'] ?? null;

        if (empty($token)) {
            throw new RuntimeException('Google Business access_token is missing.');
        }

        $accountId = $channel->platform_account_id;
        $reviewId = $contact->platform_user_id;

        $response = Http::withToken($token)
            ->put("https://mybusiness.googleapis.com/v4/{$accountId}/reviews/{$reviewId}/reply", [
                'comment' => $content,
            ]);

        $response->throw();

        return $response->json('name');
    }
}
