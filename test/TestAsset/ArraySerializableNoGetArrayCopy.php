<?php
/**
 * @see       https://github.com/zendframework/zend-hydrator for the canonical source repository
 * @copyright Copyright (c) 2010-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Hydrator\TestAsset;

class ArraySerializableNoGetArrayCopy
{
    protected $data = [];

    public function __construct()
    {
        $this->data = [
            "foo" => "bar",
            "bar" => "foo",
            "blubb" => "baz",
            "quo" => "blubb"
        ];
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->data = $array;
    }

    /**
     * Returns the internal data
     */
    public function getData()
    {
        return $this->data;
    }
}
