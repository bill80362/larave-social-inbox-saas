<?php

namespace App\Http\Middleware;

use App\Enums\Platform;
use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyGoogleBusinessSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (! str_starts_with($authHeader, 'Bearer ')) {
            abort(403, 'Missing Bearer token');
        }

        $jwt = substr($authHeader, 7);

        if (! $this->verifyGoogleJwt($jwt)) {
            abort(403, 'Invalid JWT');
        }

        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        $resourceName = $payload['name'] ?? null;

        if (! $resourceName || ! preg_match('#^accounts/([^/]+)#', $resourceName, $matches)) {
            abort(403, 'Cannot determine account ID from payload');
        }

        $accountId = $matches[1];

        $channel = Channel::where('platform', Platform::GoogleBusiness)
            ->where('platform_account_id', $accountId)
            ->first();

        if (! $channel) {
            abort(404, 'Channel not found');
        }

        $request->attributes->set('webhook_channel', $channel);

        return $next($request);
    }

    private function verifyGoogleJwt(string $jwt): bool
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode($this->base64UrlDecode($headerB64), true);

        if (($header['alg'] ?? '') !== 'RS256') {
            return false;
        }

        $kid = $header['kid'] ?? null;

        if (! $kid) {
            return false;
        }

        $publicKeys = $this->fetchGooglePublicKeys();
        $publicKey = $publicKeys[$kid] ?? null;

        if (! $publicKey) {
            return false;
        }

        $data = $headerB64.'.'.$payloadB64;
        $signature = $this->base64UrlDecode($signatureB64);

        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * @return array<string, string>
     */
    private function fetchGooglePublicKeys(): array
    {
        return Cache::remember('google_public_keys', 3600, function (): array {
            $response = Http::get('https://www.googleapis.com/oauth2/v3/certs');
            $jwks = $response->json();

            $keys = [];
            foreach ($jwks['keys'] ?? [] as $jwk) {
                $pem = $this->jwkToPem($jwk);
                if ($pem !== null) {
                    $keys[$jwk['kid']] = $pem;
                }
            }

            return $keys;
        });
    }

    private function jwkToPem(array $jwk): ?string
    {
        if (($jwk['kty'] ?? '') !== 'RSA') {
            return null;
        }

        $n = $this->base64UrlDecode($jwk['n']);
        $e = $this->base64UrlDecode($jwk['e']);

        if (ord($n[0]) > 0x7F) {
            $n = "\x00".$n;
        }

        if (ord($e[0]) > 0x7F) {
            $e = "\x00".$e;
        }

        $nSeq = "\x02".$this->asn1EncodeLength(strlen($n)).$n;
        $eSeq = "\x02".$this->asn1EncodeLength(strlen($e)).$e;
        $rsaBody = $nSeq.$eSeq;
        $rsaSeq = "\x30".$this->asn1EncodeLength(strlen($rsaBody)).$rsaBody;

        $bitStringContent = "\x00".$rsaSeq;
        $bitString = "\x03".$this->asn1EncodeLength(strlen($bitStringContent)).$bitStringContent;

        $oid = hex2bin('300d06092a864886f70d0101010500');
        $spkiBody = $oid.$bitString;
        $der = "\x30".$this->asn1EncodeLength(strlen($spkiBody)).$spkiBody;

        return "-----BEGIN PUBLIC KEY-----\n".
            chunk_split(base64_encode($der), 64, "\n").
            "-----END PUBLIC KEY-----\n";
    }

    private function asn1EncodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $encoded = '';
        while ($length > 0) {
            $encoded = chr($length & 0xFF).$encoded;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encoded)).$encoded;
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
