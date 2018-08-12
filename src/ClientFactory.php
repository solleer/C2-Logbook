<?php
namespace Solleer\C2Logbook;
use GuzzleHttp\Client;

class ClientFactory {
    private $token;
    private $env;

    public function __construct(string $token, Config $env) {
        $this->token = $token;
        $this->env = $env;
    }

    public function getClient(): Client {
        $client = new Client([
            'base_uri' => $this->env->getRestRoot() . 'api/users/',
            'headers' => [
                'Accept' => 'application/vnd.c2logbook.v1+json',
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);
        return $client;
    }
}
