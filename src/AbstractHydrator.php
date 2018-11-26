<?php
/**
 * @see       https://github.com/zendframework/zend-hydrator for the canonical source repository
 * @copyright Copyright (c) 2010-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Hydrator;

use ArrayObject;

abstract class AbstractHydrator implements
    HydratorInterface,
    StrategyEnabledInterface,
    FilterEnabledInterface,
    NamingStrategyEnabledInterface
{
    /**
     * The list with strategies that this hydrator has.
     *
     * @var ArrayObject
     */
    protected $strategies;

    /**
     * An instance of NamingStrategy\NamingStrategyInterface
     *
     * @var null|NamingStrategy\NamingStrategyInterface
     */
    protected $namingStrategy;

    /**
     * Composite to filter the methods, that need to be hydrated
     *
     * @var Filter\FilterComposite
     */
    protected $filterComposite;

    /**
     * Initializes a new instance of this class.
     */
    public function __construct()
    {
        $this->strategies = new ArrayObject();
        $this->filterComposite = new Filter\FilterComposite();
    }

    /**
     * Gets the strategy with the given name.
     *
     * @param string $name The name of the strategy to get.
     * @throws Exception\InvalidArgumentException
     */
    public function getStrategy(string $name) : Strategy\StrategyInterface
    {
        if (isset($this->strategies[$name])) {
            return $this->strategies[$name];
        }

        if ($this->hasNamingStrategy()
            && ($hydrated = $this->getNamingStrategy()->hydrate($name))
            && isset($this->strategies[$hydrated])
        ) {
            return $this->strategies[$hydrated];
        }

        if (! isset($this->strategies['*'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: no strategy by name of "%s", and no wildcard strategy present',
                __METHOD__,
                $name
            ));
        }

        return $this->strategies['*'];
    }

    /**
     * Checks if the strategy with the given name exists.
     *
     * @param string $name The name of the strategy to check for.
     */
    public function hasStrategy(string $name) : bool
    {
        if ($this->strategies->offsetExists($name)) {
            return true;
        }

        if ($this->hasNamingStrategy()
            && $this->strategies->offsetExists($this->getNamingStrategy()->hydrate($name))
        ) {
            return true;
        }

        return $this->strategies->offsetExists('*');
    }

    /**
     * Adds the given strategy under the given name.
     *
     * @param string $name The name of the strategy to register.
     * @param Strategy\StrategyInterface $strategy The strategy to register.
     */
    public function addStrategy(string $name, Strategy\StrategyInterface $strategy) : void
    {
        $this->strategies[$name] = $strategy;
    }

    /**
     * Removes the strategy with the given name.
     *
     * @param string $name The name of the strategy to remove.
     */
    public function removeStrategy(string $name) : void
    {
        unset($this->strategies[$name]);
    }

    /**
     * Converts a value for extraction. If no strategy exists the plain value is returned.
     *
     * @param  string      $name   The name of the strategy to use.
     * @param  mixed       $value  The value that should be converted.
     * @param  null|object $object The object is optionally provided as context.
     * @return mixed
     */
    public function extractValue(string $name, $value, ?object $object = null)
    {
        if ($this->hasStrategy($name)) {
            $strategy = $this->getStrategy($name);
            $value = $strategy->extract($value, $object);
        }
        return $value;
    }

    /**
     * Converts a value for hydration. If no strategy exists the plain value is returned.
     *
     * @param  string     $name  The name of the strategy to use.
     * @param  mixed      $value The value that should be converted.
     * @param  null|array $data  The whole data is optionally provided as context.
     * @return mixed
     */
    public function hydrateValue(string $name, $value, ?array $data = null)
    {
        if ($this->hasStrategy($name)) {
            $strategy = $this->getStrategy($name);
            $value = $strategy->hydrate($value, $data);
        }
        return $value;
    }

    /**
     * Convert a name for extraction. If no naming strategy exists, the plain value is returned.
     *
     * @param  string      $name    The name to convert.
     * @param  null|object $object  The object is optionally provided as context.
     * @return mixed
     */
    public function extractName(string $name, ?object $object = null)
    {
        if ($this->hasNamingStrategy()) {
            $name = $this->getNamingStrategy()->extract($name, $object);
        }
        return $name;
    }

    /**
     * Converts a value for hydration. If no naming strategy exists, the plain value is returned.
     *
     * @param  string $name  The name to convert.
     * @param  array  $data  The whole data is optionally provided as context.
     */
    public function hydrateName(string $name, ?array $data = null) : string
    {
        if ($this->hasNamingStrategy()) {
            $name = $this->getNamingStrategy()->hydrate($name, $data);
        }
        return $name;
    }

    /**
     * Get the filter instance
     */
    public function getFilter() : Filter\FilterInterface
    {
        return $this->filterComposite;
    }

    /**
     * Add a new filter to take care of what needs to be hydrated.
     * To exclude e.g. the method getServiceLocator:
     *
     * <code>
     * $composite->addFilter("servicelocator",
     *     function ($property) {
     *         list($class, $method) = explode('::', $property);
     *         if ($method === 'getServiceLocator') {
     *             return false;
     *         }
     *         return true;
     *     }, FilterComposite::CONDITION_AND
     * );
     * </code>
     *
     * @param string $name Index in the composite
     * @param callable|Filter\FilterInterface $filter
     */
    public function addFilter(string $name, $filter, int $condition = Filter\FilterComposite::CONDITION_OR) : void
    {
        $this->filterComposite->addFilter($name, $filter, $condition);
    }

    /**
     * Check whether a specific filter exists at key $name or not
     *
     * @param string $name Index/name in the composite
     */
    public function hasFilter(string $name) : bool
    {
        return $this->filterComposite->hasFilter($name);
    }

    /**
     * Remove a filter from the composition.
     *
     * To not extract "has" methods, unregister the filter.
     *
     * <code>
     * $filterComposite->removeFilter('has');
     * </code>
     */
    public function removeFilter(string $name) : void
    {
        $this->filterComposite->removeFilter($name);
    }

    /**
     * Adds the given naming strategy
     *
     * @param NamingStrategy\NamingStrategyInterface $strategy The naming to register.
     */
    public function setNamingStrategy(NamingStrategy\NamingStrategyInterface $strategy) : void
    {
        $this->namingStrategy = $strategy;
    }

    /**
     * Gets the naming strategy.
     *
     * @throws Exception\DomainException if no naming strategy is registered.
     */
    public function getNamingStrategy() : NamingStrategy\NamingStrategyInterface
    {
        if (null === $this->namingStrategy) {
            throw new Exception\DomainException(
                'Missing naming strategy; call hasNamingStrategy() to test for presence first'
            );
        }
        return $this->namingStrategy;
    }

    /**
     * Checks if a naming strategy exists.
     */
    public function hasNamingStrategy() : bool
    {
        return isset($this->namingStrategy);
    }

    /**
     * Removes the naming strategy
     */
    public function removeNamingStrategy() : void
    {
        $this->namingStrategy = null;
    }
}
