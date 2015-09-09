<?php

namespace Nercury\CodeIgniterBundle;

class CiControllerChecker
{
    /**
     * @var string
     */
    private $appPath;

    public function __construct($appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     * Get physical controller file name based on it's name
     *
     * @param string $controllerName
     *
     * @return string
     */
    private function getControllerFile($controllerName)
    {
        return $this->appPath.'/controllers/'.$controllerName.'.php';
    }

    /**
     * @param $controller
     *
     * @return bool
     */
    public function isControllerExist($controller)
    {
        $controllerFile = $this->getControllerFile($controller);

        return file_exists($controllerFile);
    }
}
