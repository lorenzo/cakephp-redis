<?php

namespace Cake\Redis;

use Cake\Database\Log\LoggedQuery;

class LoggedCommand extends LoggedQuery
{
    public $query;

    public $took = 0;

    public $numRows = 0;

    /**
     * Constructs the logged command by with th epassed called method and its arguments
     *
     * @param string $method The redis command that was invoked
     * @param array $parameters The list of arguments provided to the command
     */
    public function __construct($method, array $parameters)
    {
        $allParams = [];
        foreach ($parameters as $param) {
            if (is_array($param)) {
                $allParams = array_merge($allParams, $param);
                continue;
            }

            $allParams[] = $param;
        }

        foreach ($allParams as &$p) {
            if (!is_scalar($p)) {
                $p = sprintf('(%s)', gettype($p));
            }
        }

        $this->query = sprintf("%s %s", strtoupper($method), implode(" ", $allParams));
    }

    /**
     * Returns the string representation of this logged query
     *
     * @return string
     */
    public function __toString(): string
    {
        return "duration={$this->took} rows={$this->numRows} {$this->query}";
    }
}
