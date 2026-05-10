<x-filament-panels::page>
    @php
        $record = $this->record;
        $contact = $record->contact;
        $channel = $record->channel;
        $messages = $record->messages()->orderBy('sent_at')->get();
        $notes = $record->notes()->with('user')->orderBy('created_at')->get();
        $displayName = $contact?->name ?? $contact?->platform_user_id ?? '未知聯絡人';
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- 左側：對話訊息 --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- 聯絡人資訊卡片 --}}
            <x-filament::section>
                <x-slot name="heading">聯絡人資訊</x-slot>
                <div class="flex items-center gap-4">
                    @if ($contact?->avatar_url)
                        <img src="{{ $contact->avatar_url }}" alt="{{ $displayName }}" class="h-12 w-12 rounded-full object-cover">
                    @else
                        <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <x-filament::icon icon="heroicon-o-user" class="h-6 w-6 text-gray-500" />
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $displayName }}</p>
                        <p class="text-sm text-gray-500">{{ $channel?->name }} · {{ $channel?->platform?->label() }}</p>
                    </div>
                    <div class="ml-auto">
                        <x-filament::badge :color="match($record->status) {
                            \App\Enums\ConversationStatus::Open => 'success',
                            \App\Enums\ConversationStatus::Pending => 'warning',
                            \App\Enums\ConversationStatus::Resolved => 'danger',
                        }">
                            {{ $record->status->label() }}
                        </x-filament::badge>
                    </div>
                </div>
                @if ($record->assignedTo)
                    <p class="mt-2 text-sm text-gray-500">指派給：<span class="font-medium">{{ $record->assignedTo->name }}</span></p>
                @endif
            </x-filament::section>

            {{-- 訊息時間軸 --}}
            <x-filament::section>
                <x-slot name="heading">對話訊息</x-slot>

                @forelse ($messages as $message)
                    @php
                        $isOutbound = $message->direction === \App\Enums\MessageDirection::Outbound;
                    @endphp
                    <div class="flex {{ $isOutbound ? 'justify-end' : 'justify-start' }} mb-4">
                        <div class="max-w-[70%] rounded-2xl px-4 py-3 {{ $isOutbound ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white rounded-bl-sm' }}">
                            {{-- 訊息內容依類型渲染 --}}
                            @switch($message->type)
                                @case(\App\Enums\MessageType::Text)
                                    <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                    @break

                                @case(\App\Enums\MessageType::Image)
                                    @if ($message->content)
                                        <img src="{{ $message->content }}" alt="圖片" class="max-w-full rounded-lg">
                                    @endif
                                    @break

                                @case(\App\Enums\MessageType::Audio)
                                    @if ($message->content)
                                        <audio controls class="max-w-full">
                                            <source src="{{ $message->content }}">
                                        </audio>
                                    @endif
                                    @break

                                @case(\App\Enums\MessageType::Video)
                                    @if ($message->content)
                                        <video controls class="max-w-full rounded-lg">
                                            <source src="{{ $message->content }}">
                                        </video>
                                    @endif
                                    @break

                                @case(\App\Enums\MessageType::Sticker)
                                    <p class="text-sm text-gray-400">[貼圖]</p>
                                    @break

                                @case(\App\Enums\MessageType::File)
                                    @if ($message->content)
                                        <a href="{{ $message->content }}" target="_blank" class="flex items-center gap-2 text-sm underline">
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-4 w-4" />
                                            下載附件
                                        </a>
                                    @endif
                                    @break

                                @case(\App\Enums\MessageType::Review)
                                    <p class="text-sm whitespace-pre-wrap">⭐ {{ $message->content }}</p>
                                    @break

                                @default
                                    <p class="text-sm text-gray-400">[不支援的訊息類型]</p>
                            @endswitch

                            <p class="mt-1 text-xs {{ $isOutbound ? 'text-primary-200' : 'text-gray-400' }}">
                                {{ $message->sent_at?->format('Y/m/d H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-400 py-8">尚無訊息</p>
                @endforelse
            </x-filament::section>
        </div>

        {{-- 右側：內部備註 --}}
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">內部備註</x-slot>

                @forelse ($notes as $note)
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20 p-3 mb-3">
                        <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $note->body }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $note->user?->name }} · {{ $note->created_at?->format('Y/m/d H:i') }}</p>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-400 py-4">尚無備註</p>
                @endforelse
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
