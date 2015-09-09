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

/**
 * This is default event listener to resolve CI actions.
 * It resolves /{controller}/{function} action
 */
class DefaultCiActionResolver
{
    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var CiControllerChecker
     */
    protected $controllerChecker;

    public function __construct(CiControllerChecker $controllerChecker, $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
        $this->controllerChecker = $controllerChecker;
    }

    public function addPossibleRoutes(CiActionResolveEvent $event, &$pathParts, $indexOfFirst, $locale)
    {
        $controllerPath = '';
        for ($i = $indexOfFirst; $i < count($pathParts) && $i < 10; $i++) {
            if ($controllerPath == '') {
                $controllerPath = $pathParts[$i];
            } else {
                $controllerPath .= '/'.$pathParts[$i];
            }
            $next = $i < count($pathParts) - 1 ? $pathParts[$i + 1] : false;

            if (!$this->controllerChecker->isControllerExist($controllerPath)) {
                continue;
            }

            $event->addPossibleAction(
                $controllerPath,
                $next !== false ? $next : 'index',
                $locale
            );
        }
    }

    /**
     * This method collects possible routes for a request.
     *
     * @param CiActionResolveEvent $event
     */
    public function onActionResolveEvent(CiActionResolveEvent $event)
    {
        $path = $event->getRequest()->getPathInfo();
        $part = (string) substr($path, 1);
        if ($part === '') {
            $part = 'home/';
        }

        $parts = explode('/', $part);
        if (count($parts) === 1) {
            $parts[] = '';
        }
        $indexOfFirst = 0;

        if (count($parts) > 1) {
            if (false !== strpos($parts[0], '.php')) {
                $indexOfFirst++;
            }

            if (count($parts) > $indexOfFirst + 1) {
                $this->addPossibleRoutes($event, $parts, $indexOfFirst, $this->defaultLocale);

                // add routes in case first part is a language string, i.e /en/...
                if (count($parts) > $indexOfFirst + 2 && strlen($parts[0]) > 1 && strlen($parts[0]) <= 2) {
                    $this->addPossibleRoutes($event, $parts, $indexOfFirst + 1, $parts[0]);
                }
            }
        }
    }
}
