<?php

namespace App\Http\Middleware;

use App\Enums\Platform;
use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLineSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Line-Signature');

        if (! $signature) {
            abort(403, 'Missing X-Line-Signature header');
        }

        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        $destination = $payload['destination'] ?? null;

        if (! $destination) {
            abort(403, 'Missing destination in payload');
        }

        $channel = Channel::where('platform', Platform::Line)
            ->where('platform_account_id', $destination)
            ->first();

        if (! $channel) {
            abort(404, 'Channel not found');
        }

        $credentials = json_decode($channel->credentials, true);
        $channelSecret = $credentials['channel_secret'] ?? null;

        if (! $channelSecret) {
            abort(500, 'Channel secret not configured');
        }

        $computed = base64_encode(hash_hmac('sha256', $rawBody, $channelSecret, true));

        if (! hash_equals($computed, $signature)) {
            abort(403, 'Invalid signature');
        }

        $request->attributes->set('webhook_channel', $channel);

        return $next($request);
    }
}
