<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case Image = 'image';
    case Sticker = 'sticker';
    case Audio = 'audio';
    case Video = 'video';
    case File = 'file';
    case Review = 'review';
}
