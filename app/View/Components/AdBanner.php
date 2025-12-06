<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdBanner extends Component
{
    public $location;

    public function __construct($location)
    {
        $this->location = $location;
    }

    public function render()
    {
        $ad = \App\Models\Ad::active()
            ->location($this->location)
            ->inRandomOrder()
            ->first();

        if ($ad) {
            $ad->increment('views');
        }

        return view('components.ad-banner', compact('ad'));
    }
}
