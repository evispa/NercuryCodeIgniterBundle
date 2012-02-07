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
        $index_of_first = 0;
        
        if (count($parts) > 1) {
            if (false !== strpos($parts[0], '.php')) {
                $index_of_first++;
            }

            if (count($parts) > $index_of_first + 1) {
                $controller_path = '';
                for ($i = $index_of_first; $i < count($parts) && $i < 10; $i++) {
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
                if (count($parts) > $index_of_first + 2 && strlen($parts[0]) > 1 && strlen($parts[0]) <= 2) {
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

}