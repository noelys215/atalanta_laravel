<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class ToggleIsAdmin extends Component
{
    public $userId;
    public $isAdmin;

    public function mount($userId, $isAdmin)
    {
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
    }

    public function toggle()
    {
        $user = User::find($this->userId);
        $user->is_admin = !$user->is_admin;
        $user->save();
        $this->isAdmin = $user->is_admin;
    }

    public function render()
    {
        return view('livewire.toggle-is-admin');
    }
}
