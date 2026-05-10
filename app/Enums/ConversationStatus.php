<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            ConversationStatus::Open => 'Open',
            ConversationStatus::Pending => 'Pending',
            ConversationStatus::Resolved => 'Resolved',
        };
    }
}
