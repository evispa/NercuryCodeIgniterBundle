<?php

namespace Nercury\CodeIgniterBundle;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of CiRequestListenerService
 *
 * @author nercury
 */
class CiRequestListenerService {

    /**
     * 
     * @var CiHelperService 
     */
    private $ci_helper;
    
    public function __construct($ci_helper) {
        $this->ci_helper = $ci_helper;
    }
    
    public function onKernelRequest(GetResponseEvent $event) {
        if ($this->ci_helper->hasMethod('aa', 'bb')) {
            
        }
        
        $event->setResponse(new Response('aa'));
    }
    
}