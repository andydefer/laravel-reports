<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum ReportType: string implements EnumerableInterface
{
    case SPAM = 'spam';
    case HARASSMENT = 'harassment';
    case INAPPROPRIATE = 'inappropriate';
    case MISINFORMATION = 'misinformation';
    case COPYRIGHT = 'copyright';
    case OTHER = 'other';

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SPAM => 'Spam',
            self::HARASSMENT => 'Harcèlement',
            self::INAPPROPRIATE => 'Contenu inapproprié',
            self::MISINFORMATION => 'Désinformation',
            self::COPYRIGHT => 'Violation de droits d\'auteur',
            self::OTHER => 'Autre',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SPAM => 'yellow',
            self::HARASSMENT => 'red',
            self::INAPPROPRIATE => 'orange',
            self::MISINFORMATION => 'purple',
            self::COPYRIGHT => 'blue',
            self::OTHER => 'gray',
        };
    }
}
