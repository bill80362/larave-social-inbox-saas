<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFacebookWebhook;
use App\Jobs\ProcessGoogleBusinessWebhook;
use App\Jobs\ProcessInstagramWebhook;
use App\Jobs\ProcessLineWebhook;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    /** @var array<string, class-string> */
    private array $jobMap = [
        'line' => ProcessLineWebhook::class,
        'facebook' => ProcessFacebookWebhook::class,
        'instagram' => ProcessInstagramWebhook::class,
        'google_business' => ProcessGoogleBusinessWebhook::class,
    ];

    public function handle(Request $request): Response
    {
        $channel = $request->attributes->get('webhook_channel');

        if (! $channel instanceof Channel || ! $channel->is_active) {
            return response('', 200);
        }

        $jobClass = $this->jobMap[$channel->platform->value] ?? null;

        if ($jobClass === null) {
            return response('', 200);
        }

        dispatch(new $jobClass($channel, $request->all()));

        return response('', 200);
    }

    public function challenge(Request $request): Response
    {
        $verifyToken = config('services.facebook.webhook_verify_token');

        if (
            $request->query('hub_mode') === 'subscribe' &&
            $request->query('hub_verify_token') === $verifyToken
        ) {
            return response($request->query('hub_challenge', ''), 200);
        }

        abort(403, 'Invalid verify token');
    }
}
