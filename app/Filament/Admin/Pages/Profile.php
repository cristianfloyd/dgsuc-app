<?php

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class Profile extends Page
{
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected string $view = 'filament.admin.pages.profile';

    protected static ?string $title = 'Mi Perfil';

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'profile_photo_path' => auth()->user()->profile_photo_path,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ComponentsGrid::make(2)
                    ->schema([
                        // Columna izquierda: datos del usuario
                        Section::make('Datos del usuario')
                            ->columns(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                                TextInput::make('current_password')
                                    ->label('Contraseña actual')
                                    ->password()
                                    ->revealable(true)
                                    ->dehydrated(false),
                                TextInput::make('new_password')
                                    ->label('Nueva contraseña')
                                    ->password()
                                    ->revealable(true)
                                    ->dehydrated(false),
                                TextInput::make('new_password_confirmation')
                                    ->label('Confirmar nueva contraseña')
                                    ->password()
                                    ->revealable(true)
                                    ->dehydrated(false),
                            ]),
                        // Columna derecha: foto de perfil
                        Section::make('Foto de perfil')
                            ->columns(1)
                            ->schema([
                                FileUpload::make('profile_photo_path')
                                    ->label('Foto de perfil')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('profile-photos')
                                    ->maxSize(1024),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = auth()->user();

        $validated = $this->form->getState();

        // Validar contraseña actual si se intenta cambiar
        if ($validated['new_password']) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                Notification::make()
                    ->title('La contraseña actual es incorrecta')
                    ->danger()
                    ->send();

                return;
            }

            $user->password = Hash::make($validated['new_password']);
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'profile_photo_path' => $validated['profile_photo_path'],
        ])->save();

        Notification::make()
            ->title('Perfil actualizado exitosamente')
            ->success()
            ->send();
    }
}
