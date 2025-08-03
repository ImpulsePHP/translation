<?php

declare(strict_types=1);

namespace Impulse\Translation\Contract;

interface TranslatorInterface
{
    public function trans(string $key, array $replacements = []): string;
    public function transWithFallback(string $key, array $fallbackKeys = [], array $replacements = []): string;
    public function setLocale(string $locale): void;
    public function getLocale(): string;
    public function addNamespace(string $namespace, string $path): void;
    public function loadNamespaceTranslations(string $namespace, string $path): void;
}
