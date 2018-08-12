<?php
namespace Solleer\C2Logbook;
class ProviderFactory {
    private $clientID;
    private $clientSecret;
    private $redirectUriPath;
    private $env;

    public function __construct(Config $env, $clientId, $clientSecret, $redirectUriPath) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUriPath = $redirectUriPath;
        $this->env = $env;
    }

    public function getProvider() {
        return new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $this->clientId,
            'clientSecret'            => $this->clientSecret,
            'redirectUri'             => $this->redirectUriPath,
            'urlAuthorize'            => $this->env->getRestRoot() . 'oauth/authorize',
            'urlAccessToken'          => $this->env->getRestRoot() . 'oauth/access_token',
            'urlResourceOwnerDetails' => '',
            'scope'                  => 'user:read,results:read'
        ]);
    }
}
