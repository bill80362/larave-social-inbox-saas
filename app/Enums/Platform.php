<?php

namespace App\Enums;

enum Platform: string
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Line = 'line';
    case GoogleBusiness = 'google_business';

    public function label(): string
    {
        return match ($this) {
            Platform::Instagram => 'Instagram',
            Platform::Facebook => 'Facebook',
            Platform::Line => 'LINE',
            Platform::GoogleBusiness => 'Google Business',
        };
    }
}
