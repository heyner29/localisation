<?php

namespace LaravelEnso\Localisation\App\Http\Controllers\Json;

use Illuminate\Http\Request;
use LaravelEnso\Localisation\App\Models\Language;
use LaravelEnso\Localisation\App\Services\Json\Updater;

class Update
{
    public function __invoke(Request $request, Language $language, string $subDir)
    {
        (new Updater(
            $language, $request->get('langFile'), $subDir
        ))->run();

        return ['message' => __('The language files were successfully updated')];
    }
}
