<?php
namespace Solleer\C2Logbook;
use League\OAuth2\Client\Provider\GenericProvider;
class Authentication {
    private $provider;

    public function __construct(GenericProvider $provider) {
        $this->provider = $provider;
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getToken($requestType, array $properties) {
        try {
            $accessToken = $this->provider->getAccessToken($requestType, $properties);
            return $accessToken;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function refreshToken($refreshToken) {
        return $this->getToken('refresh_token', [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]);
    }

    public function getAccessToken($code) {
        return $this->getToken('authorization_code', ['code' => $code]);
    }
}
