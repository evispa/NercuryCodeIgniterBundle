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
        if ($event->getRequestType() == \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST)
            return;
        
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