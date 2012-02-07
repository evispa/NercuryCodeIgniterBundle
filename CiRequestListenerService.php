<?php

namespace Nercury\CodeIgniterBundle;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listens to kernel request event and gets response from CI in case
 * a controller matches URL.
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
    
    /**
     * This method listens to symfony request, and if it's url matches some controller
     * defined in CI path, it redirects request handling to CI.
     * 
     * @param GetResponseEvent $event 
     */
    public function onKernelRequest(GetResponseEvent $event) {
        $actions = $this->ci_helper->resolveCiActions($event->getRequest());
        foreach ($actions as $action) {
            if ($this->ci_helper->hasController($action['controller'])) {
                // handle everything over CI
                $event->setResponse($this->ci_helper->getResponse($event->getRequest()));
                $event->stopPropagation();
                break;
            }
        }
    }
    
}