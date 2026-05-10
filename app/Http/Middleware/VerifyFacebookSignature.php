<?php

namespace App\Http\Middleware;

use App\Enums\Platform;
use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFacebookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (! $signature) {
            abort(403, 'Missing X-Hub-Signature-256 header');
        }

        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        $pageId = $payload['entry'][0]['id'] ?? null;

        if (! $pageId) {
            abort(403, 'Cannot determine page ID from payload');
        }

        $channel = Channel::where('platform', Platform::Facebook)
            ->where('platform_account_id', $pageId)
            ->first();

        if (! $channel) {
            abort(404, 'Channel not found');
        }

        $credentials = json_decode($channel->credentials, true);
        $appSecret = $credentials['app_secret'] ?? null;

        if (! $appSecret) {
            abort(500, 'App secret not configured');
        }

        $computed = 'sha256='.hash_hmac('sha256', $rawBody, $appSecret);

        if (! hash_equals($computed, $signature)) {
            abort(403, 'Invalid signature');
        }

        $request->attributes->set('webhook_channel', $channel);

        return $next($request);
    }
}
