<?php

namespace Magenest\SpecialCustomerProgram\Api;

interface SetCiInformationInterface
{
    /**
     * @param string[] $param
     * @return true
     */
    public function execute(array $param);
    /**
     * @param string[] $param
     * @return true
     */
    public function remove(array $param);
}
