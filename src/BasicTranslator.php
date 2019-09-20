<?php

namespace RecAnalyst;

/**
 * Super tiny default translator for when RecAnalyst is used outside of Laravel.
 * Uses translations provided with RecAnalyst, with no possibility of
 * customisation.
 */
class BasicTranslator
{
    /**
     * Current locale.
     *
     * @var string
     */
    private $locale;

    /**
     * Translations for the current locale.
     *
     * @var array
     */
    private $translations = [];

    /**
     *
     */
    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
    }

    /**
     * @param string  $key  Translation key.
     */
    public function trans(string $key): string
    {
        list ($file, $var) = explode('.', $key, 2);
        if (!isset($this->translations[$file])) {
            $current = $this->getFilePath($this->locale, $file);
            // Default to English
            if (!is_file($current) && $this->locale !== 'en') {
                $current = $this->getFilePath('en', $file);
            }
            $this->translations[$file] = require($current);
        }
        return $this->get($this->translations[$file], $var);
    }

    /**
     * Get the path to a translation file.
     */
    private function getFilePath(string $locale, string $file): string
    {
        return __DIR__ . '/../resources/lang/' . $locale . '/' . $file . '.php';
    }

    /**
     * Get a value from a dotted property path.
     *
     * @param array  $arr  Array.
     * @param string  $path  Path, properties separated by '.'s.
     * @return mixed (But probably a string because it's for translations.)
     */
    private function get(array $arr, string $path)
    {
        foreach (explode('.', $path) as $prop) {
            $arr = $arr[$prop];
        }
        return $arr;
    }
}
