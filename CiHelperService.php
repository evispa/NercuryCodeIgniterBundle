<?php

/*
 * Copyright 2012 Nerijus Arlauskas <nercury@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Nercury\CodeIgniterBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Kernel;
use Monolog\Logger;

/**
 * Description of CiHelperService
 *
 * @author nercury
 */
class CiHelperService
{
    /**
     * @var bool
     */
    protected $detectControllers;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     *
     * @var Kernel
     */
    protected $kernel;

    /**
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $systemPath;

    /**
     * @var bool
     */
    private $pathsInitialized = false;

    /**
     * @var bool
     */
    private $overrideControllerClass = false;

    /**
     * @var bool
     */
    private static $ciLoaded = false;

    public function __construct(
        $detectControllers,
        $applicationPath,
        $systemPath,
        Logger $logger,
        Kernel $kernel,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->detectControllers = $detectControllers;
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->appPath = realpath($applicationPath);
        $this->systemPath = realpath($systemPath);
    }

    /**
     * @return bool
     */
    protected function isConfigValid()
    {
        return !is_null($this->appPath) && !is_null($this->systemPath);
    }

    /**
     * Initialize CodeIgniter system paths and defines
     *
     * @param Request $request
     *
     * @throws \UnexpectedValueException
     */
    public function setCiPaths(Request $request = null)
    {
        if (!$this->isConfigValid()) {
            throw new \UnexpectedValueException(
                'Bundle configuration is not valid. You need to specify application_path and system_path in config.yml'
            );
        }

        if ($this->pathsInitialized === false) {
            $scriptFile = $request !== null ?
                '.'.$request->getBasePath().$request->getScriptName()
                : __FILE__;

            $rootPath = realpath($this->kernel->getRootDir().'/..');
            if ($rootPath === false) {
                throw new \LogicException('Nercury CI bundle was expecting to find kernel root dir in /app directory.');
            }

            $systemPath = $this->getSystemPath().'/';
            $applicationFolder = $this->getAppPath().'/';

            if ($scriptFile === __FILE__) {
                $scriptFile = $rootPath.'/app.php';
                $rootPath = realpath($rootPath.'/'.$systemPath).'/';
                $applicationFolder = realpath($rootPath.'/'.$applicationFolder);
            }

            $environment = $this->kernel->getEnvironment();
            $environmentMap = array('dev' => 'development', 'test' => 'testing', 'prod' => 'production');
            if (array_key_exists($environment, $environmentMap)) {
                $environment = $environmentMap[$environment];
            }
            define('ENVIRONMENT', $environment);

            /*
            * -------------------------------------------------------------------
            *  Now that we know the path, set the main path constants
            * -------------------------------------------------------------------
            */
            // The name of THIS file
            define('SELF', pathinfo($scriptFile, PATHINFO_BASENAME));

            // The PHP file extension
            // this global constant is deprecated.
            define('EXT', '.php');

            // Path to the system folder
            define('BASEPATH', str_replace("\\", "/", $systemPath));

            // Name of the "system folder"
            define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

            define('APPPATH', $applicationFolder.'/');

            // Path to the front controller (this file)
            define('FCPATH', $applicationFolder.'../');

//            if (!defined('APPPATH')) {
//                // The path to the "application" folder
//                if (is_dir($application_folder)) {
//                    define('APPPATH', $application_folder . '/');
//                } else {
//                    if (!is_dir(BASEPATH . $application_folder . '/')) {
//                        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
//                    }
//
//                    define('APPPATH', BASEPATH . $application_folder . '/');
//                }
//            }

            $this->pathsInitialized = true;
        }
    }

    /**
     * @param string $className
     *
     * @return self
     */
    public function setBaseControllerOverrideClass($className)
    {
        $this->overrideControllerClass = $className;

        return $this;
    }

    /**
     * @param bool $useFakeController
     *
     * @return \CI_Controller
     * @throws \Exception
     */
    public function getInstance($useFakeController = true)
    {
        $this->unsetNoticeErrorLevel();

        if (function_exists('get_instance')) {
            self::$ciLoaded = true;
        }

        if (!self::$ciLoaded) {
            self::$ciLoaded = true;

            if ($this->kernel->getContainer()->isScopeActive('request')) {
                $this->setCiPaths($this->kernel->getContainer()->get('request'));
            } else {
                $this->setCiPaths();
            }

            require_once __DIR__.'/ci_bootstrap.php';

            \ci_bootstrap(
                $this->kernel,
                $this->overrideControllerClass,
                $useFakeController
            ); // load without calling CodeIgniter method but initiating CI class
        }

        return \get_instance();
    }

    /**
     * Return response from CI
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function getResponse(Request $request)
    {
        if (self::$ciLoaded) {
            throw new \Exception(
                'Can not create response for CodeIgniter controller, because another controller was already loaded.'
            );
        }

        self::$ciLoaded = true;

        $this->unsetNoticeErrorLevel();
        $this->setCiPaths($request);

        require_once __DIR__.'/ci_bootstrap.php';

        ob_start();

        /*
         * --------------------------------------------------------------------
         * LOAD THE BOOTSTRAP FILE
         * --------------------------------------------------------------------
         *
         * And away we go...
         *
         */
        \ci_bootstrap($this->kernel);

        $response = new Response(ob_get_clean(), http_response_code(), $this->extractHeaders());

        return $response;
    }

    /**
     * Get current response headers.
     *
     * @return array
     */
    private function extractHeaders()
    {
        $list = array();

        $headers = headers_list();

        foreach ($headers as $header) {
            $header = explode(':', $header, 2);
            $name = array_shift($header);
            if (!isset($list[$name])) {
                $list[$name] = [];
            }
            $list[$name][] = trim(implode(':', $header));
        }

        header_remove();

        return $list;
    }

    /**
     * Returns CI APPPATH
     *
     * @return string Returns FALSE if path was not defined in config
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * Returns CI system path
     *
     * @return string Returns FALSE if path was not defined in config
     */
    public function getSystemPath()
    {
        return $this->systemPath;
    }

    /**
     * @return self
     */
    public function unsetNoticeErrorLevel()
    {
        // code igniter likes notices
        $errorLevel = error_reporting();
        if ($errorLevel > 0) {
            error_reporting($errorLevel & ~E_NOTICE);
        } elseif ($errorLevel < 0) {
            error_reporting(E_ALL & ~E_NOTICE);
        }

        return $this;
    }
}

