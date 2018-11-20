<?php
/**
 * @link      http://github.com/zendframework/zend-hydrator for the canonical source repository
 * @copyright Copyright (c) 2010-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

declare(strict_types=1);

namespace Zend\Hydrator;

use Zend\ModuleManager\ModuleManager;

class Module
{
    /**
     * Return default zend-hydrator configuration for zend-mvc applications.
     */
    public function getConfig() : array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register a specification for the HydratorManager with the ServiceListener.
     */
    public function init(ModuleManager $moduleManager) : void
    {
        $event = $moduleManager->getEvent();
        $container = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'HydratorManager',
            'hydrators',
            HydratorProviderInterface::class,
            'getHydratorConfig'
        );
    }
}
