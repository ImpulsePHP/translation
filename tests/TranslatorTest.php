<?php

declare(strict_types=1);

namespace Impulse\Translation\Tests;

use Impulse\Translation\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    private string $root;
    private string $namespaceDir;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
        $this->namespaceDir = $this->root . '/namespace_translations';
        @mkdir("{$this->namespaceDir}/en", 0777, true);
        file_put_contents("{$this->namespaceDir}/en/messages.php", "<?php\nreturn ['ns' => 'Namespaced'];\n");

        chdir(__DIR__);
    }

    protected function tearDown(): void
    {
        @unlink("{$this->namespaceDir}/en/messages.php");
        @rmdir("{$this->namespaceDir}/en");
        @rmdir($this->namespaceDir);
        chdir($this->root);
    }

    public function testBasicTranslation(): void
    {
        $translator = new Translator('fr');
        $this->assertSame('Bonjour', $translator->trans('messages.hello'));
    }

    public function testFallbackTranslation(): void
    {
        $translator = new Translator('fr');
        $this->assertSame('Welcome John', $translator->trans('messages.welcome', ['name' => 'John']));
    }

    public function testMissingKeyReturnsOriginal(): void
    {
        $translator = new Translator('fr');
        $this->assertSame('messages.unknown', $translator->trans('messages.unknown'));
    }

    public function testLocaleSwitch(): void
    {
        $translator = new Translator('fr');
        $translator->setLocale('en');
        $this->assertSame('Hello', $translator->trans('messages.hello'));
    }

    public function testNamespacedTranslation(): void
    {
        $translator = new Translator('en');
        $translator->addNamespace('test', $this->namespaceDir);
        $this->assertSame('Namespaced', $translator->trans('test::messages.ns'));
    }
}
