<?php

declare(strict_types=1);

namespace Impulse\Translation;

use Impulse\Core\Container\ImpulseContainer;
use Impulse\Core\Provider\AbstractProvider;
use Impulse\Translation\Contract\TranslatorInterface;

final class TranslatorProvider extends AbstractProvider
{
    /**
     * @throws \JsonException
     */
    public function registerServices(ImpulseContainer $container): void
    {
        $container->set(TranslatorInterface::class, fn () => new Translator());
    }
}
