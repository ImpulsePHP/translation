<?php

declare(strict_types=1);

namespace Impulse\Translation;

use Impulse\Core\Support\Config;
use Impulse\Translation\Contract\TranslatorInterface;

final class Translator implements TranslatorInterface
{
    private string $locale;
    private string $fallback = 'en';
    private array $translations = [];
    private array $namespaces = [];

    /**
     * @throws \JsonException
     */
    public function __construct(?string $locale = null)
    {
        $this->locale = $locale ?? $this->detectLocale();
        $this->loadTranslations();
    }

    public function trans(string $key, array $replacements = []): string
    {
        $value = $this->resolveTranslation($key);
        if (!is_string($value)) {
            return $key;
        }

        return $this->replaceParameters($value, $replacements);
    }

    public function transWithFallback(string $key, array $fallbackKeys = [], array $replacements = []): string
    {
        $value = $this->resolveTranslation($key);

        if (is_string($value)) {
            return $this->replaceParameters($value, $replacements);
        }

        foreach ($fallbackKeys as $fallbackKey) {
            $value = $this->resolveTranslation($fallbackKey);

            if (is_string($value)) {
                return $this->replaceParameters($value, $replacements);
            }
        }

        return $key;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;

        $this->loadTranslations();
        $this->reloadNamespaces();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function addNamespace(string $namespace, string $path): void
    {
        $this->namespaces[$namespace] = $path;
        $this->loadNamespaceTranslations($namespace, $path);
    }

    public function loadNamespaceTranslations(string $namespace, string $path): void
    {
        foreach ([$this->locale, $this->fallback] as $lang) {
            $langPath = "$path/$lang";

            if (!is_dir($langPath)) {
                continue;
            }

            foreach (glob("$langPath/*.php") as $file) {
                $domain = basename($file, '.php');
                $this->translations[$lang]["_namespaces"][$namespace][$domain] = require $file;
            }
        }
    }

    private function resolveTranslation(string $key): ?string
    {
        if (str_contains($key, '::')) {
            [$namespace, $localKey] = explode('::', $key, 2);
            return $this->resolveNamespacedKey($namespace, $localKey);
        }

        $value = $this->resolveKey($key, $this->translations[$this->locale] ?? []);

        if ($value === null && $this->locale !== $this->fallback) {
            $value = $this->resolveKey($key, $this->translations[$this->fallback] ?? []);
        }

        return $value;
    }

    private function replaceParameters(string $value, array $replacements): string
    {
        foreach ($replacements as $k => $v) {
            $replacement = $v === null ? '' : (string) $v;
            $value = str_replace("{" . $k . "}", $replacement, $value);
        }

        return $value;
    }

    /**
     * @throws \JsonException
     */
    private function detectLocale(): string
    {
        return $_GET['lang']
            ?? ($_SESSION['lang'] ?? null)
            ?? $this->getLocaleFromHeaders()
            ?? Config::get('locale')
            ?? $this->fallback;
    }

    /**
     * @throws \JsonException
     */
    private function getLocaleFromHeaders(): ?string
    {
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (isset($acceptLang) && in_array($acceptLang, Config::get('supported', ['en']), true)) {
            return $acceptLang;
        }

        return null;
    }

    private function loadTranslations(): void
    {
        $base = dirname(getcwd()) . '/translations';

        $this->translations = [];

        foreach ([$this->locale, $this->fallback] as $lang) {
            $this->translations[$lang] = ['_namespaces' => []];

            $path = "$base/$lang";
            if (!is_dir($path)) {
                continue;
            }

            foreach (glob("$path/*.php") as $file) {
                $domain = basename($file, '.php');
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }

                $this->translations[$lang][$domain] = require $file;
            }
        }
    }

    private function reloadNamespaces(): void
    {
        foreach ($this->namespaces as $namespace => $path) {
            $this->loadNamespaceTranslations($namespace, $path);
        }
    }

    private function resolveKey(string $key, array $data): ?string
    {
        $segments = explode('.', $key);
        $domain = array_shift($segments);

        $value = $data[$domain] ?? null;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return is_string($value) ? $value : null;
    }

    private function resolveNamespacedKey(string $namespace, string $key): ?string
    {
        $value = $this->resolveKey($key, $this->translations[$this->locale]['_namespaces'][$namespace] ?? []);

        if ($value === null && $this->locale !== $this->fallback) {
            $value = $this->resolveKey($key, $this->translations[$this->fallback]['_namespaces'][$namespace] ?? []);
        }

        return $value;
    }
}
