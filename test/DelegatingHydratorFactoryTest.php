<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Hydrator;

use Zend\Hydrator\DelegatingHydratorFactory;

/**
 * @covers Zend\Hydrator\DelegatingHydratorFactory<extended>
 */
class DelegatingHydratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $hydratorManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $factory = new DelegatingHydratorFactory();
        $this->assertInstanceOf(
            'Zend\Hydrator\DelegatingHydrator',
            $factory->createService($hydratorManager)
        );
    }
}
