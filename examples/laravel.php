<?php

namespace App\Http\Controllers;

use RecAnalyst\RecordedGame;
use Illuminate\Http\Request;

/**
 * An example recorded game analysis controller.
 */
class RecAnalystController extends Controller
{
    /**
     * Render a PNG map image and send it to the browser.
     */
    public function showMapImage(Request $request)
    {
        // The RecordedGame constructor accepts SplFileInfo instances,
        // so uploaded files can be passed straight to it:
        $rec = new RecordedGame($request->file('game'));

        // `mapImage()` returns an instance of \Intervention\Image, which has a
        // Laravel-compatible `response()` method.
        return $rec->mapImage()->response('png');
    }
}
