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
use Composer\Util\Filesystem;

/**
 * Description of SymfonyBundleTests
 *
 * @author Pierre-Gildas MILLON <pierre-gildas.millon@ineat-conseil.fr>
 */
class SymfonyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    
    const BUNDLE_VENDOR = 'TestCompany';
    const BUNDLE_NAME = 'TestBundle';
    
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
        $handler = new ScriptHandlerTest($this->getPostCreateEvent());
        $handler->setParameters([
            ScriptHandler::PARAMETER_VENDOR => new Parameter('', self::BUNDLE_VENDOR),
            ScriptHandler::PARAMETER_BUNDLE => new Parameter('', self::BUNDLE_NAME),
        ]);
        $handler->buildBundleClass();
        $bundleClass = file_get_contents($handler->getBundleClassFile());
        $this->assertRegExp(sprintf('/namespace %s\\\\%s;/', self::BUNDLE_VENDOR, self::BUNDLE_NAME), $bundleClass);
        $this->assertRegExp(sprintf('/class %s extends Bundle/', self::BUNDLE_VENDOR.self::BUNDLE_NAME), $bundleClass);
    }
    
    /**
     * @test
     */
    public function iCanGetProjectPath()
    {
        $filesystem = new Filesystem();
        $config = $this->getPostCreateEvent()->getComposer()->getConfig();
        $basePath = $filesystem->normalizePath(getcwd());
        $vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
        $appBaseDirCode = $filesystem->findShortestPathCode($vendorPath, $basePath, true);
        $appBaseDirCode = str_replace('__DIR__', '$vendorDir', $appBaseDirCode);
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
}
