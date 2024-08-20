<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteConfirmationModal extends Component
{
    public $modalId;
    public $actionUrl;
    public $title;
    public $message;

    public function __construct($modalId, $actionUrl, $title, $message)
    {
        $this->modalId = $modalId;
        $this->actionUrl = $actionUrl;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.delete-confirmation-modal');
    }
}
