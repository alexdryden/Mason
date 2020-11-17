<?php

namespace Mason;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



    public function chosen(Event $event)
    {
        $view = $event->getTarget();
        $view->headScript()->appendFile($view->assetUrl('js/chose.js', 'Mason'));

    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            '*',
            'view.layout',
            [$this, 'chosen']
        );
    }

}