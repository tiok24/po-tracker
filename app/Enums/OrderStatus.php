<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
    case Waiting   = 'Waiting';
    case OnProcess = 'On Process';
    case Completed = 'Completed';
    case Delayed   = 'Delayed';
    case Hold      = 'Hold';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Waiting   => 'gray',
            self::OnProcess => 'info',
            self::Completed => 'success',
            self::Delayed   => 'danger',
            self::Hold      => 'warning',
        };
    }

    /** Status yang boleh dipilih admin (Delayed otomatis). */
    public static function selectable(): array
    {
        return [self::Waiting, self::OnProcess, self::Completed, self::Hold];
    }
}