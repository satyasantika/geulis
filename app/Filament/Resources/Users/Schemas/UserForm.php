<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama')
                ->required()
                ->maxLength(255),
            TextInput::make('username')
                ->label('Username (NIS / NIP)')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),
            TextInput::make('email')
                ->label('Email (opsional)')
                ->email()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label('Kata sandi / PIN')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ]);
    }
}
