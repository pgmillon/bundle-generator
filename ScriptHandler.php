<?php

namespace Symfony\BundleGenerator;

use Symfony\Component\Filesystem\Filesystem;
use Composer\Script\Event;
use \Twig_Environment;
use \Twig_Loader_Filesystem;

/**
 * Description of ScriptHandler
 *
 * @author Pierre-Gildas MILLON <pierre-gildas.millon@ineat-conseil.fr>
 */
class ScriptHandler
{
    const PARAMETER_VENDOR = 'vendor';
    const PARAMETER_BUNDLE = 'bundle';

    /**
     *
     * @var Composer\Script\Event
     */
    protected $event;

    /**
     *
     * @var Twig_Environment
     */
    protected $twig;
    protected $parameters = [];

    /**
     * 
     * @param Composer\Script\Event $event
     */
    public static function buildClasses(Event $event)
    {
        $handler = new ScriptHandler($event);
        $handler->readParameters();
        $handler->buildBundleClass();
        $handler->buildExtensionClass();
        $handler->buildConfigurationClass();
        $handler->buildComposerFile();
    }

    /**
     * 
     * @param Composer\Script\Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->parameters = [
            self::PARAMETER_VENDOR => new Parameter('Vendor name', 'Acme'),
            self::PARAMETER_BUNDLE => new Parameter('Bundle name', 'MyBundle'),
        ];
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/Resources/templates');
        $this->twig = new Twig_Environment($loader);
    }

    public function readParameters()
    {
        $io = $this->getEvent()->getIO();

        foreach ($this->getParameters() as $param) {
            $question = sprintf('<question>%s</question> (<comment>%s</comment>)', $param->getMessage(), $param->getDefaultValue());
            $param->setValue($io->ask($question, $param->getDefaultValue()));
        }
    }

    public function buildBundleClass()
    {
        $this->buildFile('Bundle.php.twig', $this->getBundleClassFile());
    }

    public function buildExtensionClass()
    {
        $this->buildFile('Extension.php.twig', $this->getExtensionClassFile());
    }

    public function buildConfigurationClass()
    {
        $this->buildFile('Configuration.php.twig', $this->getConfigurationClassFile());
    }
    
    public function buildComposerFile()
    {
        $this->buildFile('composer.json.twig', $this->getComposerFile());
    }
    
    public function buildTestAppKernelFile()
    {
        $this->buildFile('AppKernel.php.twig', $this->getTestAppKernelFile());
    }
    
    public function buildTestServicesFile()
    {
        $this->buildFile('services.xml.twig', $this->getTestServicesFile());
    }
    
    protected function buildFile($templateFile, $filename)
    {
        $fs = new Filesystem();
        $content = $this->getTwig()->render($templateFile, ['parameters' => $this->getParameters()]);
        $fs->mkdir(dirname($filename));
        file_put_contents($filename, $content);
    }

    /**
     * 
     * @return Composer\Script\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * 
     * @return Parameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($parameterName)
    {
        if (isset($this->parameters[$parameterName])) {
            $parameter = $this->parameters[$parameterName];
            /* @var $parameter Parameter */
            return $parameter->getValue();
        } else {
            return null;
        }
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * 
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
    
    public function getRootDir()
    {
        return getcwd();
    }

    public function getBundleClassFile()
    {
        return $this->getRootDir() . '/' . $this->getParameter(self::PARAMETER_VENDOR) . $this->getParameter(self::PARAMETER_BUNDLE) . '.php';
    }

    public function getExtensionClassFile()
    {
        $filename = $this->getParameter(self::PARAMETER_VENDOR);
        $filename .= str_replace('Bundle', '', $this->getParameter(self::PARAMETER_BUNDLE));
        $filename .= 'Extension';
        return $this->getRootDir() . '/DependencyInjection/' . $filename . '.php';
    }

    public function getConfigurationClassFile()
    {
        return $this->getRootDir() . '/DependencyInjection/Configuration.php';
    }
    
    public function getComposerFile()
    {
        return $this->getRootDir() . '/composer.json';
    }
    
    public function getTestAppKernelFile()
    {
        return $this->getRootDir() . '/Tests/Fixtures/app/AppKernel.php';
    }
    
    public function getTestServicesFile()
    {
        return $this->getRootDir() . '/Resources/config/services.xml';
    }
    
}
