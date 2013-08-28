<?php

namespace Symfony\BundleGenerator\Tests;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\BundleGenerator\Tests\ScriptHandlerTest;
use Symfony\BundleGenerator\ScriptHandler;
use Symfony\BundleGenerator\Parameter;
use Composer\IO\ConsoleIO;
use Composer\Factory;
use Composer\Script\Event;

/**
 * Description of SymfonyBundleTests
 *
 * @author Pierre-Gildas MILLON <pierre-gildas.millon@ineat-conseil.fr>
 */
class SymfonyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    
    const BUNDLE_VENDOR = 'TestCompany';
    const BUNDLE_NAME = 'Test';
    
    static function getBundleName($suffix = 'Bundle') {
        return self::BUNDLE_NAME.$suffix;
    }
    
    /**
     * 
     * @test
     */
    public function iCanRunPostCreateProjectScript()
    {
        $handler = new ScriptHandlerTest($this->getPostCreateEvent());
        $this->assertNotNull($handler);
    }
    
    /**
     * @test
     */
    public function iCanGenerateBundleClass()
    {
        $handler = $this->getHandler();
        $handler->buildBundleClass();
        $bundleClass = file_get_contents($handler->getBundleClassFile());
        $this->assertRegExp(sprintf('/namespace %s\\\\%s;/', self::BUNDLE_VENDOR, self::getBundleName()), $bundleClass);
        $this->assertRegExp(sprintf('/class %s extends Bundle/', self::BUNDLE_VENDOR.self::getBundleName()), $bundleClass);
    }
    
    /**
     * @test
     */
    public function iCanGenerateExtensionClass()
    {
        $handler = $this->getHandler();
        $handler->buildExtensionClass();
        $bundleClass = file_get_contents($handler->getExtensionClassFile());
        $this->assertRegExp(sprintf('/namespace %s\\\\%s\\\\DependencyInjection;/', self::BUNDLE_VENDOR, self::getBundleName()), $bundleClass);
        $this->assertRegExp(sprintf('/class %sExtension extends Extension/', self::BUNDLE_VENDOR.self::getBundleName('')), $bundleClass);
    }
    
    /**
     * @test
     */
    public function iCanGenerateConfigurationClass()
    {
        $handler = $this->getHandler();
        $handler->buildConfigurationClass();
        $bundleClass = file_get_contents($handler->getConfigurationClassFile());
        $this->assertRegExp(sprintf('/namespace %s\\\\%s\\\\DependencyInjection;/', self::BUNDLE_VENDOR, self::getBundleName()), $bundleClass);
        $this->assertRegExp(sprintf('/\\$rootNode = \\$treeBuilder->root\\(\'%s_%s\'\\)/', strtolower(self::BUNDLE_VENDOR), strtolower(str_replace('Bundle', '', self::getBundleName()))), $bundleClass);
    }
    
    /**
     * @test
     */
    public function iCanGenerateComposerFile()
    {
        $handler = $this->getHandler();
        $handler->buildComposerFile();
        $bundleClass = file_get_contents($handler->getComposerFile());
        $this->assertRegExp(sprintf('/"name": "%s\\/%s",/', strtolower(self::BUNDLE_VENDOR), strtolower(self::getBundleName())), $bundleClass);
        $this->assertRegExp(sprintf('/"%s\\\\\\\\%s": ""/', self::BUNDLE_VENDOR, self::getBundleName()), $bundleClass);
        $this->assertRegExp(sprintf('/"target-dir": "%s\\/%s"/', self::BUNDLE_VENDOR, self::getBundleName()), $bundleClass);
    }
    
    protected function getIO()
    {
        $styles = Factory::createAdditionalStyles();
        $formatter = new OutputFormatter(null, $styles);
        $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
        return new ConsoleIO(new ArgvInput(), $output, new HelperSet(array(
            new FormatterHelper(),
            new DialogHelper(),
            new ProgressHelper(),
        )));
    }

    protected function getPostCreateEvent()
    {
        $io = $this->getIO();
        $composer = Factory::create($io);
        return new Event('post-create-project-cmd', $composer, $io);
    }
    
    protected function getHandler()
    {
        $handler = new ScriptHandlerTest($this->getPostCreateEvent());
        $handler->setParameters([
            ScriptHandler::PARAMETER_VENDOR => new Parameter('', self::BUNDLE_VENDOR),
            ScriptHandler::PARAMETER_BUNDLE => new Parameter('', self::getBundleName()),
        ]);
        return $handler;
    }
}
