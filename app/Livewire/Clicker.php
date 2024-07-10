<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Clicker extends Component
{
    use WithPagination;

    #[Rule(['required', 'min:5','max:100'])]
    public $name;
    #[Rule(['required', 'email', 'unique:users', 'max:255'])]
    public $email;
    #[Rule(['required', 'min:6'])]
    public $password;

    public function createNewUser()
    {
        $validated = $this->validate();
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
        $this->reset('name', 'email', 'password');
        session()->flash('success', 'User created successfully.');
    }

    public function render()
    {
        $users = User::orderByDesc('created_at')->paginate(5);
        // dd($users);
        return view('livewire.clicker',[
            'users' => $users
        ]);
    }
}
