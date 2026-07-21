<?php

namespace App\Livewire;

use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DisplayNamePrompt extends Component
{
    public string $displayName = '';

    public bool $show = false;

    public string $message = '';

    public string $messageType = 'error';

    public function mount(): void
    {
        $this->show = true;
        $user = Auth::user();
        if ($user) {
            $this->displayName = $user->name ?? '';
        }
    }

    public function saveDisplayName(): void
    {
        $this->validate([
            'displayName' => 'required|string|min:2|max:255',
        ]);

        $sessionId = session('user_session_id');
        if ($sessionId) {
            UserSession::where('id', $sessionId)->update([
                'display_name' => $this->displayName,
            ]);
        }

        $this->show = false;
        $this->dispatch('display-name-saved');
    }

    public function render()
    {
        return view('livewire.display-name-prompt');
    }
}
