<?php


namespace Mason\Service\Form\Element;


use Interop\Container\ContainerInterface;
use Mason\Form\Element\AllTeamSelect;
use Mason\Form\Element\TeamSelect;

class AllTeamSelectFactory
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new AllTeamSelect(null, $options);
        $element->setApiManager($services->get('Omeka\ApiManager'));
        $element->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
        $element->setEntityManager($services->get('Omeka\EntityManager'));
        return $element;
    }
}