<?php

namespace Ergo\Http;

/**
 * Represents the query string portion of a url
 */
class QueryString
{
    private $_params = array();
    private $_default = false;

    /**
     * Constructor
     */
    function __construct($queryString)
    {
        parse_str($queryString, $this->_params);
    }

    /**
     * Sets the default value that is returned if a parameter isn't set
     */
    function setDefaultValue($value)
    {
        $this->_default = $value;
    }

    /**
     * Returns an array version of the query string
     */
    function toArray()
    {
        return $this->_params;
    }

    /**
     * Adds params
     * @param array $newParams
     */
    function addParameters($newParams)
    {
        $this->_params = array_merge($this->_params, $newParams);
    }

    /**
     * Returns a particular key, with a specific default value
     */
    function value($key, $default = false)
    {
        return $this->__isset($key) ? $this->__get($key) : $default;
    }

    function __toString()
    {
        return http_build_query($this->toArray());
    }

    /**
     * Magic method for property getters
     */
    function __get($prop)
    {
        return isset($this->_params[$prop]) ?
            $this->_params[$prop] : $this->_default;
    }

    /**
     * Magic method for property setters
     */
    function __set($prop, $value)
    {
        $this->_params[$prop] = $value;
    }

    /**
     * Magic method, determines whether the property exists
     */
    function __isset($prop)
    {
        return isset($this->_params[$prop]);
    }

    /**
     * Magic method, unsets a property
     */
    function __unset($prop)
    {
        unset($this->_params[$prop]);
    }
}
