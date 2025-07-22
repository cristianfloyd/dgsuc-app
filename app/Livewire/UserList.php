<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    #[Url(as : 's', history: true)]
    public $search;

    public $selectedUser;

    #[On('user-created')]
    public function updateList(): void
    {

    }

    public function viewUser(User $user): void
    {
        $this->selectedUser = $user;
        // dd($this->selectedUser);
        $this->dispatch('open-modal', name: 'user-details');
    }

    public function render()
    {
        $users = User::latest()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(5);
        return view('livewire.user-list', [
            'users' => $users,
        ]);
    }

    #[Computed()]
    public function users()
    {
        return User::latest()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(5);
    }
}
