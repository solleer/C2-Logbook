<?php
namespace Solleer\C2Logbook;

class Config {
    private $env;

    const DEV = 'http://log-dev.concept2.com/';
    const PROD = 'http://log.concept2.com/';

    public function __construct($env = self::PROD) {
        $this->env = $env;
    }

    public function getRestRoot() {
        return $this->env;
    }
}
