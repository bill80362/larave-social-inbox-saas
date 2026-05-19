<?php

namespace App\Filament\Resources\Channels;

use App\Enums\Platform;
use App\Filament\Resources\Channels\Pages\CreateChannel;
use App\Filament\Resources\Channels\Pages\EditChannel;
use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Models\Channel;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
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
        return $schema->components([
            TextInput::make('name')
                ->label('頻道名稱')
                ->required()
                ->maxLength(255),

            Select::make('platform')
                ->label('平台')
                ->options(collect(Platform::cases())->mapWithKeys(fn (Platform $p) => [$p->value => $p->label()]))
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('channel_secret', null);
                    $set('channel_access_token', null);
                    $set('destination', null);
                    $set('verify_token', null);
                    $set('page_access_token', null);
                    $set('access_token', null);
                    $set('account_id', null);
                }),

            TextInput::make('platform_account_id')
                ->label('平台帳號 ID')
                ->required()
                ->maxLength(255)
                ->unique(
                    table: Channel::class,
                    column: 'platform_account_id',
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule
                        ->where('workspace_id', auth()->user()?->workspace_id)
                        ->where('platform', $get('platform'))
                ),

            // LINE credentials
            TextInput::make('channel_secret')
                ->label('Channel Secret')
                ->visible(fn (Get $get): bool => $get('platform') === Platform::Line->value)
                ->maxLength(255),

            TextInput::make('channel_access_token')
                ->label('Channel Access Token')
                ->password()
                ->revealable()
                ->visible(fn (Get $get): bool => $get('platform') === Platform::Line->value)
                ->maxLength(1024),

            TextInput::make('destination')
                ->label('Destination (LINE User ID)')
                ->visible(fn (Get $get): bool => $get('platform') === Platform::Line->value)
                ->maxLength(255),

            // Facebook / Instagram credentials
            TextInput::make('verify_token')
                ->label('Verify Token')
                ->visible(fn (Get $get): bool => in_array($get('platform'), [Platform::Facebook->value, Platform::Instagram->value]))
                ->maxLength(255),

            TextInput::make('page_access_token')
                ->label('Page Access Token')
                ->password()
                ->revealable()
                ->visible(fn (Get $get): bool => in_array($get('platform'), [Platform::Facebook->value, Platform::Instagram->value]))
                ->maxLength(1024),

            // Google Business credentials
            TextInput::make('access_token')
                ->label('Access Token')
                ->password()
                ->revealable()
                ->visible(fn (Get $get): bool => $get('platform') === Platform::GoogleBusiness->value)
                ->maxLength(1024),

            TextInput::make('account_id')
                ->label('Account ID')
                ->visible(fn (Get $get): bool => $get('platform') === Platform::GoogleBusiness->value)
                ->maxLength(255),

            Toggle::make('is_active')
                ->label('啟用中')
                ->default(true),
        ]);
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChannels::route('/'),
            'create' => CreateChannel::route('/create'),
            'edit' => EditChannel::route('/{record}/edit'),
        ];
    }
}
