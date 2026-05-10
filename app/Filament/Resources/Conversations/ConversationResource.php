<?php

namespace App\Filament\Resources\Conversations;

use App\Enums\ConversationStatus;
use App\Enums\Platform;
use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Models\Conversation;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = '收件匣';

    protected static ?string $navigationLabel = '對話';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Conversation::query()
                    ->with(['contact', 'channel', 'assignedTo'])
            )
            ->columns([
                TextColumn::make('contact.name')
                    ->label('聯絡人')
                    ->formatStateUsing(fn ($state, Conversation $record): string => $state ?? $record->contact?->platform_user_id ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('contact', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                            ->orWhere('platform_user_id', 'like', "%{$search}%"));
                    }),

                TextColumn::make('channel.name')
                    ->label('頻道'),

                BadgeColumn::make('channel.platform')
                    ->label('平台')
                    ->formatStateUsing(fn ($state): string => $state instanceof Platform ? $state->label() : (string) $state)
                    ->colors([
                        'info' => Platform::Instagram->value,
                        'primary' => Platform::Facebook->value,
                        'success' => Platform::Line->value,
                        'warning' => Platform::GoogleBusiness->value,
                    ]),

                BadgeColumn::make('status')
                    ->label('狀態')
                    ->formatStateUsing(fn ($state): string => $state instanceof ConversationStatus ? $state->label() : (string) $state)
                    ->colors([
                        'success' => ConversationStatus::Open->value,
                        'warning' => ConversationStatus::Pending->value,
                        'danger' => ConversationStatus::Resolved->value,
                    ]),

                TextColumn::make('assignedTo.name')
                    ->label('指派給')
                    ->default('—'),

                TextColumn::make('last_message_at')
                    ->label('最後訊息')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('狀態')
                    ->options(
                        collect(ConversationStatus::cases())
                            ->mapWithKeys(fn (ConversationStatus $s) => [$s->value => $s->label()])
                            ->toArray()
                    ),

                SelectFilter::make('platform')
                    ->label('平台')
                    ->options(
                        collect(Platform::cases())
                            ->mapWithKeys(fn (Platform $p) => [$p->value => $p->label()])
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            $query->whereHas('channel', fn (Builder $q) => $q->where('platform', $data['value']));
                        }

                        return $query;
                    }),

                SelectFilter::make('assigned_to')
                    ->label('指派給')
                    ->options(function (): array {
                        if (! auth()->hasUser()) {
                            return [];
                        }

                        return User::where('workspace_id', auth()->user()->workspace_id)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'view' => ViewConversation::route('/{record}'),
        ];
    }
}
