<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            Select::make('roles')
                ->label('Peran')
                ->relationship('roles', 'label')
                ->multiple()
                ->preload()
                ->required()
                ->helperText('Hanya admin dan peneliti yang dapat membuka panel ini.'),
            Toggle::make('aktif')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
