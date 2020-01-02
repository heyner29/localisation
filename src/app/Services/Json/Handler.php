<?php

namespace LaravelEnso\Localisation\App\Services\Json;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\App\Classes\JsonParser;
use LaravelEnso\Localisation\App\Models\Language;
use LaravelEnso\Localisation\App\Services\Traits\JsonFilePathResolver;

abstract class Handler
{
    use JsonFilePathResolver;

    protected function newTranslations(array $array): Collection
    {
        return (new Collection($array))
            ->mapWithKeys(fn ($key) => [$key => null]);
    }

    protected function saveMerged(string $locale, array $langFile): void
    {
        $this->saveToDisk($locale, $langFile);
    }

    protected function savePartial(string $locale, array $langFile, string $subDir): void
    {
        $this->saveToDisk($locale, $langFile, $subDir);
    }

    protected function saveToDisk(string $locale, array $langFile, ?string $subDir = null): void
    {
        File::put(
            $this->jsonFileName($locale, $subDir),
            json_encode($langFile, JSON_FORCE_OBJECT | ($subDir ? JSON_PRETTY_PRINT : 0))
        );
    }

    protected function merge(?string $locale = null): void
    {
        $languages = Language::extra();

        if ($locale) {
            $languages->where('name', $locale);
        }

        $languages->pluck('name')
            ->each(fn ($locale) => $this->mergeLocale($locale));
    }

    private function mergeLocale(string $locale): void
    {
        $core = (new JsonParser($this->coreJsonFileName($locale)))->array();
        $app = (new JsonParser($this->appJsonFileName($locale)))->array();

        $this->saveMerged($locale, array_merge($core, $app));
    }
}
