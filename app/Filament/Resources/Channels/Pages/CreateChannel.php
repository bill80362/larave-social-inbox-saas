<?php

namespace App\Filament\Resources\Channels\Pages;

use App\Filament\Resources\Channels\ChannelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChannel extends CreateRecord
{
    protected static string $resource = ChannelResource::class;

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['workspace_id'] = auth()->user()->workspace_id;

        return self::encodeCredentials($data);
    }

    /** @param array<string, mixed> $data */
    public static function encodeCredentials(array $data): array
    {
        $credentialKeys = ['channel_secret', 'channel_access_token', 'destination', 'verify_token', 'page_access_token', 'access_token', 'account_id'];

        $credentials = [];
        foreach ($credentialKeys as $key) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $credentials[$key] = $data[$key];
            }
            unset($data[$key]);
        }

        $data['credentials'] = json_encode($credentials);

        return $data;
    }
}
