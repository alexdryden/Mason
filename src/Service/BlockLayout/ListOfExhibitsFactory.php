<?php


namespace Mason\Service\BlockLayout;


use Interop\Container\ContainerInterface;
use Mason\Site\BlockLayout\ListOfExhibits;
use Zend\ServiceManager\Factory\FactoryInterface;

class ListOfExhibitsFactory implements FactoryInterface
{
    /**
     * Create the ListOfExhibits block layout service.
     *
     * @param ContainerInterface $serviceLocator
     * @return ListOfExhibits
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $entityManager = $serviceLocator->get('Omeka\EntityManager');
        $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
//        $navTranslator = $serviceLocator->get('Omeka\Site\Navigation\Translator');
        return new ListOfExhibits($entityManager, $moduleManager);
    }

}