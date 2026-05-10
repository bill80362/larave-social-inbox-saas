<?php

namespace App\Filament\Resources\Channels;

use App\Enums\Platform;
use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Models\Channel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = '收件匣';

    protected static ?string $navigationLabel = '頻道';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('頻道名稱')
                    ->searchable(),

                BadgeColumn::make('platform')
                    ->label('平台')
                    ->formatStateUsing(fn ($state): string => $state instanceof Platform ? $state->label() : (string) $state)
                    ->colors([
                        'info' => Platform::Instagram->value,
                        'primary' => Platform::Facebook->value,
                        'success' => Platform::Line->value,
                        'warning' => Platform::GoogleBusiness->value,
                    ]),

                TextColumn::make('platform_account_id')
                    ->label('平台帳號 ID')
                    ->copyable(),

                IconColumn::make('is_active')
                    ->label('啟用中')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChannels::route('/'),
        ];
    }
}
