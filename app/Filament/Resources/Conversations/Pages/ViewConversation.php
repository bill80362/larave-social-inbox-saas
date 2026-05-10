<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Actions\Reply\SendReplyAction;
use App\Enums\ConversationStatus;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Note;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected string $view = 'filament.conversations.view';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('回覆')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->schema([
                    Textarea::make('content')
                        ->label('回覆內容')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    try {
                        (new SendReplyAction)($this->record, $data['content'], auth()->user());
                        Notification::make()
                            ->title('回覆已送出')
                            ->success()
                            ->send();
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('回覆失敗')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('changeStatus')
                ->label('變更狀態')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Select::make('status')
                        ->label('狀態')
                        ->options(
                            collect(ConversationStatus::cases())
                                ->mapWithKeys(fn (ConversationStatus $s) => [$s->value => $s->label()])
                                ->toArray()
                        )
                        ->default(fn () => $this->record->status->value)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['status' => $data['status']]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('assign')
                ->label('指派')
                ->icon('heroicon-o-user-plus')
                ->schema([
                    Select::make('assigned_to')
                        ->label('指派給')
                        ->options(function (): array {
                            return User::where('workspace_id', auth()->user()->workspace_id)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->default(fn () => $this->record->assigned_to)
                        ->placeholder('未指派'),
                ])
                ->action(function (array $data): void {
                    $this->record->update(['assigned_to' => $data['assigned_to'] ?: null]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('addNote')
                ->label('新增備註')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Textarea::make('body')
                        ->label('備註內容')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    Note::create([
                        'conversation_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'body' => $data['body'],
                    ]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
