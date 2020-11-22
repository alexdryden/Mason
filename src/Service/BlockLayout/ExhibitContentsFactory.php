<?php


namespace Mason\Service\BlockLayout;


use Interop\Container\ContainerInterface;
use Mason\Site\BlockLayout\ExhibitContents;
use Zend\ServiceManager\Factory\FactoryInterface;

class ExhibitContentsFactory implements FactoryInterface
{
    /**
     * Create the ListOfExhibits block layout service.
     *
     * @param ContainerInterface $serviceLocator
     * @param $requestedName
     * @param array|null $options
     * @return ExhibitContents
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $entityManager = $serviceLocator->get('Omeka\EntityManager');
        $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
//        $navTranslator = $serviceLocator->get('Omeka\Site\Navigation\Translator');
        return new ExhibitContents($entityManager, $moduleManager);
    }

}