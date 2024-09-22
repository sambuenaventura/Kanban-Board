<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskModal extends Component
{
    /**
     * Create a new component instance.
     */
    public string $modalType;
    public $board;
    public $collaborators; 
    public $nonCollaborators; 

    public function __construct(string $modalType, $board = null, $collaborators = null, $nonCollaborators = null)
    {
        $this->modalType = $modalType;
        $this->board = $board;
        $this->collaborators = $collaborators;
        $this->nonCollaborators = $nonCollaborators;
    }
    

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // Boards>Modals>Task
        if ($this->modalType === 'create') {
            return view('boards.modals.task.create-task-modal', ['board' => $this->board]);
        }
        if ($this->modalType === 'manage-collaborator') {
            return view('boards.modals.task.manage-task-collaborator-modal', [
                'board' => $this->board,
                'collaborators' => $this->collaborators,
                'nonCollaborators' => $this->nonCollaborators,
            ]);
        }
        if ($this->modalType === 'delete-board-task') {
            return view('boards.modals.task.delete-task-modal');
        }

        if ($this->modalType === 'delete-task') {
            return view('boards.tasks.modals.delete-task-modal');
        }
        if ($this->modalType === 'delete-attachment') {
            return view('boards.tasks.modals.delete-task-attachment-modal');
        }
        
    }
    
}
