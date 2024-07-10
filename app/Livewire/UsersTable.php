<?php

namespace App\Livewire;

use App\Models\dh01;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $perPage = 5;
    #[Url(history: true)]
    public $sortField = 'nro_legaj';
    #[Url(history: true)]
    public $sortDir = 'DESC';
    #[Url(history: true)]
    public $admin = '';

    public function setSortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'ASC';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function deleteUser(User $user)
    {
        $user->delete();
    }

    public function render()
    {
        return view('livewire.users-table',[
            'users' => dh01::search($this->search)
                // ->when($this->admin !== '', function ($query) {
                //     return $query->where('is_admin', $this->admin);
                // })
                ->orderBy($this->sortField, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }
}
