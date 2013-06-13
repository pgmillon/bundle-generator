<?php

namespace Symfony\BundleGenerator\Tests;

use Symfony\BundleGenerator\ScriptHandler;

/**
 * Description of ScriptHandlerTest
 *
 * @author Pierre-Gildas MILLON <pierre-gildas.millon@ineat-conseil.fr>
 */
class ScriptHandlerTest extends ScriptHandler
{
    protected $rootDir;

    public function getRootDir()
    {
        if (is_null($this->rootDir)) {
            $this->rootDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(mt_rand());
            mkdir($this->rootDir);
        }
        return $this->rootDir;
    }

}
