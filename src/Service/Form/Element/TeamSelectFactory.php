<?php


namespace Mason\Service\Form\Element;


use Mason\Form\Element\TeamSelect;
use Interop\Container\ContainerInterface;

class TeamSelectFactory
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new TeamSelect(null, $options);
        $element->setApiManager($services->get('Omeka\ApiManager'));
//        $element->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
        $element->setEntityManager($services->get('Omeka\EntityManager'));
//        $element->setAuthService($services->get('Omeka\AuthenticationService'));
//        $element->setModuleManager($services->get('Omeka\ModuleManager'));
        return $element;
    }
}

