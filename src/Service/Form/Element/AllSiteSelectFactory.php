<?php


namespace Mason\Service\Form\Element;


use Interop\Container\ContainerInterface;
use Mason\Form\Element\AllItemSetSelect;
use Mason\Form\Element\AllSiteSelect;

class AllSiteSelectFactory
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new AllSiteSelect(null, $options);
        $element->setApiManager($services->get('Omeka\ApiManager'));
        $element->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
        $element->setEntityManager($services->get('Omeka\EntityManager'));
        return $element;
    }


}