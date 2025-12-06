<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Ad;

class AdController extends Controller
{
    public function click(Ad $ad)
    {
        $ad->increment('clicks');
        return redirect()->away($ad->url);
    }
}
