<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class RegisterForm extends Component
{
    use WithFileUploads;

    #[Rule('required|min:3|max:50')]
    public $name;

    #[Rule('required|email|max:255|unique:users,email')]
    public $email;

    #[Rule('required|min:6|max:255')]
    public $password;

    #[Rule('nullable|sometimes|image|max:1024')]
    public $image;

    #[Rule('accepted')]
    public $terms = false;

    public function createUser(): void
    {

        sleep(10); // Simulate a slow request (3 seconds

        $validated = $this->validate();

        if ($this->image) {
            $validated['image'] = $this->image();
        }
        $user = User::create($validated);
        $this->reset('name', 'email', 'password', 'image', 'terms');

        session()->flash('success', 'User created successfully.');
        $this->dispatch('user-created', $user);
        $this->dispatch('close-modal');
    }

    public function reloadList(): void
    {
        $this->dispatch('user-created');
    }

    public function render()
    {
        return view('livewire.register-form');
    }

    protected function image()
    {
        try {
            return $this->image->store('images', 'public');
        } catch (\Exception $e) {
            $this->addError('image', 'Image is required and must be less than 10MB');
        }
    }
}
