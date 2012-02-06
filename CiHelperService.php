<?php

namespace Nercury\CodeIgniterBundle;

/**
 * Description of CiHelperService
 *
 * @author nercury
 */
class CiHelperService {
    
    /**
     * @var array 
     */
    private $config;
    
    /**
     * @var \Monolog\Logger 
     */
    private $logger;
    
    private $app_path = false;
    private $system_path = false;
    
    public function __construct($config, $logger) {
        $this->config = $config;
        $this->logger = $logger;
        $this->app_path = realpath($config['application_path'].'as');
        $this->system_path = realpath($config['system_path']);
    }
    
    private function isConfigValid() {
        return $this->app_path !== false && $this->system_path !== false;
    }
    
    public function hasMethod($controller, $method) {
        if (!$this->isConfigValid())
            return false;
        
    }
    
    public function forwardTo($controller, $method) {
        if (!$this->isConfigValid())
            return false;
        
    }
    
}
