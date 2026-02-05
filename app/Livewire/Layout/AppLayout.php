<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class AppLayout extends Component
{
    public bool $sidebarOpen = false;

    public function toggleSidebar(): void
    {
        $this->sidebarOpen = ! $this->sidebarOpen;
    }

    public function closeSidebar(): void
    {
        $this->sidebarOpen = false;
    }

    public function render()
    {
        return view('livewire.layout.app-layout');
    }
}
