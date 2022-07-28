<?php

namespace Magenest\Pandago\Api;

interface CallbackInterface
{
    /**
     * Execute callback.
     *
     * @return array
     */
    public function execute();
}
