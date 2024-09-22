<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BoardModal extends Component
{
    /**
     * Create a new component instance.
     */
    public string $modalType;

    public function __construct(string $modalType)
    {
        $this->modalType = $modalType;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // Boards>Modals>Board
        if ($this->modalType === 'create') {
            return view('boards.modals.board.create-board-modal');
        }
        if ($this->modalType === 'update') {
            return view('boards.modals.board.update-board-modal');
        }
        if ($this->modalType === 'delete') {
            return view('boards.modals.board.delete-board-modal');
        }
    }
}
