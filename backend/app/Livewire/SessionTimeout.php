<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SessionTimeout extends Component
{
    public int $remainingSeconds = 0;

    public bool $showWarning = false;

    public bool $expired = false;

    protected int $timeoutMinutes = 5;

    protected int $warningMinutes = 2;

    public function mount(): void
    {
        if (! Session::has('last_activity_at')) {
            Session::put('last_activity_at', now()->timestamp);
        }
        $this->checkSession();
    }

    public function checkSession(): void
    {
        if (! Auth::check()) {
            $this->expired = true;

            return;
        }

        $lastActivity = Session::get('last_activity_at', now()->timestamp);
        $elapsed = now()->timestamp - $lastActivity;
        $totalSeconds = $this->timeoutMinutes * 60;
        $this->remainingSeconds = max(0, $totalSeconds - $elapsed);

        $warningThreshold = $this->warningMinutes * 60;
        $this->showWarning = $this->remainingSeconds > 0 && $this->remainingSeconds <= $warningThreshold;

        if ($this->remainingSeconds <= 0) {
            $this->expired = true;
            $this->logout();
        }
    }

    public function extendSession(): void
    {
        Session::put('last_activity_at', now()->timestamp);
        $this->remainingSeconds = $this->timeoutMinutes * 60;
        $this->showWarning = false;
        $this->expired = false;

        $this->dispatch('session-extended');
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->expired = true;
        $this->dispatch('session-expired');
    }

    public function render()
    {
        return view('livewire.session-timeout');
    }
}
