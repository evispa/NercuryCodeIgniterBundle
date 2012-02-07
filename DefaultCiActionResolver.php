<?php

namespace Nercury\CodeIgniterBundle;

use Nercury\CodeIgniterBundle\CiActionResolveEvent;

/**
 * This is default event listener to resolve CI actions.
 * It resolves /{controller}/{function} action
 */
class DefaultCiActionResolver {

    /**
     * This method collects possible routes for a request.
     * 
     * @param CiActionResolveEvent $event 
     */
    public function onActionResolveEvent(CiActionResolveEvent $event) {
        $path = $event->getRequest()->getPathInfo();
        $parts = explode('/', substr($path, 1));
        if (count($parts) > 1) {
            $controller_path = '';
            for ($i = 0; $i < count($parts) && $i < 10; $i++) {
                if ($controller_path == '')
                    $controller_path = $parts[$i];
                else
                    $controller_path .= '/'.$parts[$i];
                $next = $i < count($parts) - 1 ? $parts[$i + 1] : false;
                if ($next !== false)
                    $event->addPossibleAction($controller_path, $next);
                $event->addPossibleAction($controller_path, 'index');
            }
            
            // add routes in case first part is a language string, i.e /en/...
            if (count($parts) > 2 && strlen($parts[0]) > 1 && strlen($parts[0]) <= 2) {
                $controller_path = '';
                for ($i = 1; $i < count($parts) && $i < 10; $i++) {
                    if ($controller_path == '')
                        $controller_path = $parts[$i];
                    else
                        $controller_path .= '/'.$parts[$i];
                    $next = $i < count($parts) - 1 ? $parts[$i + 1] : false;
                    if ($next !== false)
                        $event->addPossibleAction($controller_path, $next);
                    $event->addPossibleAction($controller_path, 'index');
                }
            }
        }
    }

}