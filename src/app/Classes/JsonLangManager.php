<?php

namespace LaravelEnso\Localisation\app\Classes;

use LaravelEnso\Localisation\app\Models\Language;

class JsonLangManager
{
    public function getContent($locale)
    {
        return json_decode(\File::get(resource_path('lang/'.$locale.'.json')));
    }

    public function update($locale, $langFile)
    {
        $this->saveToDisk($locale, $langFile);
        $this->processDifferences($locale, $langFile);

        return ['message' => __('Operation was successfull')];
    }

    public function createEmptyLangFile($locale)
    {
        $language = Language::extra()->orderBy('id')->first();
        $langFile = (array) $this->getContent($language->name);
        $langFile = $this->clearArrayValues($langFile);
        $this->saveToDisk($locale, $langFile);
    }

    public function rename($oldName, $newName)
    {
        return $oldName !== $newName
            ? \File::move(resource_path('lang').'/'.$oldName.'.json', resource_path('lang').'/'.$newName.'.json')
            : false;
    }

    public function delete($langFile)
    {
        \File::delete(resource_path('lang/'.$langFile.'.json'));
    }

    private function saveToDisk($locale, $langFile)
    {
        \File::put(resource_path('lang/'.$locale.'.json'), json_encode($langFile));
    }

    private function processDifferences($locale, $newLangFile)
    {
        $locales = Language::extra()->where('name', '<>', $locale)->pluck('name');

        $locales->each(function ($locale) use ($newLangFile) {
            $this->updateDifferences($locale, $newLangFile);
        });
    }

    private function updateDifferences(string $locale, array $newLangFile)
    {
        $langFile = (array) $this->getContent($locale);
        list($removedCount, $langFile) = $this->removeExtraKeys($langFile, $newLangFile);
        list($addedCount, $langFile) = $this->addNewKeys($langFile, $newLangFile);

        if ($addedCount || $removedCount) {
            $this->saveToDisk($locale, $langFile);
        }
    }

    private function removeExtraKeys(array $langFile, array $newLangFile)
    {
        $keysToRemove = array_diff_key($langFile, $newLangFile);

        foreach (array_keys($keysToRemove) as $keyToRemove) {
            unset($langFile[$keyToRemove]);
        }

        return [count($keysToRemove), $langFile];
    }

    private function addNewKeys(array $langFile, $newLangFile)
    {
        $keysToAdd = array_diff_key($newLangFile, $langFile);
        $arrayToAdd = $this->clearArrayValues($keysToAdd);
        $langFile = array_merge($arrayToAdd, $langFile);

        return [count($keysToAdd), $langFile];
    }

    private function clearArrayValues($array)
    {
        $keys = array_keys($array);
        $values = array_fill(0, count($keys), null);
        $newArray = array_combine($keys, $values);

        return $newArray;
    }
}
