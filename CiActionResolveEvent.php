<?php

namespace Nercury\CodeIgniterBundle;

use Symfony\Component\EventDispatcher\Event;

class CiActionResolveEvent extends Event {

    /**
     *
     * @var Request 
     */
    protected $request;
    
    /**
     * Possible CI controllers and methods
     * 
     * @var array 
     */
    protected $possible_methods = array();
    
    public function __construct($request) {
        $this->request = $request;
    }
    
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Request 
     */
    public function getRequest() {
        return $this->request;
    }
    
    /**
     * Set possible controller and method for current request
     * 
     * @param string $controller
     * @param string $method 
     */
    public function addPossibleAction($controller, $method) {
        $this->possible_methods[] = array(
            'controller' => $controller,
            'method' => $method,
        );
    }
    
    /**
     * Get resolved possible controllers and methods
     * 
     * @return array
     */
    public function getResolvedActions() {
        return $this->possible_methods;
    }
}