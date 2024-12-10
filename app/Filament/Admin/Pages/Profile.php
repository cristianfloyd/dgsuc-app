<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Filament\Admin\Resources\UserResource;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $title = 'Mi Perfil';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'profile_photo_path' => auth()->user()->profile_photo_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
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
                    ->dehydrated(false),

                TextInput::make('new_password')
                    ->label('Nueva contraseña')
                    ->password()
                    ->dehydrated(false),

                TextInput::make('new_password_confirmation')
                    ->label('Confirmar nueva contraseña')
                    ->password()
                    ->dehydrated(false),

                FileUpload::make('profile_photo_path')
                    ->label('Foto de perfil')
                    ->image()
                    ->directory('profile-photos')
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $user = auth()->user();

        $validated = $this->form->getState();

        // Validar contraseña actual si se intenta cambiar
        if ($validated['new_password']) {
            if (! Hash::check($validated['current_password'], $user->password)) {
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
            'profile_photo_path' => $validated['profile_photo_path']
        ])->save();

        Notification::make()
            ->title('Perfil actualizado exitosamente')
            ->success()
            ->send();
    }
}
