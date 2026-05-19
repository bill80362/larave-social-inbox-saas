<?php

namespace App\Filament\Resources\Channels\Pages;

use App\Filament\Resources\Channels\ChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChannel extends EditRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['credentials']) && $data['credentials'] !== null) {
            $credentials = json_decode($data['credentials'], true) ?? [];
            foreach ($credentials as $key => $value) {
                $data[$key] = $value;
            }
        }

        unset($data['credentials']);

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CreateChannel::encodeCredentials($data);
    }
}
