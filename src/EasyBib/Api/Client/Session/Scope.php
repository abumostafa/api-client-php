<?php

namespace EasyBib\Api\Client\Session;

class Scope
{
    /**
     * @var array
     */
    private $scopes;

    /**
     * @param array $scopes
     */
    public function __construct(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @return array
     */
    public function getQuerystringParams()
    {
        if (!$this->scopes) {
            return [];
        }

        return ['scope' => implode(' ', $this->scopes)];
    }
}
