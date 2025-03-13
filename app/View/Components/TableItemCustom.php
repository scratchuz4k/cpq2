<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TableItemCustom extends Component
{
    public $component;
    public $item;

    public function __construct($component = null, $item = null)
    {
        $this->component = $component;
        $this->item = $item;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table-item-custom');
    }
}
