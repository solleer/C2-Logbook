<?php
namespace Solleer\C2Logbook;
use Solleer\User\{SigninStatus};

class OAuthSignin {
    private $model;
    private $config;
    private $userStatus;
    private $oauthStatus;

    public function __construct(Authentication $model, Config $config, SigninStatus $userStatus, OAuthStatus $oauthStatus) {
        $this->model = $model;
        $this->config = $config;
        $this->userStatus = $userStatus;
        $this->oauthStatus = $oauthStatus;
    }

    public function signin($code, $setCookie = false) {
        $accessToken = $this->model->getAccessToken($code);
if (!$accessToken) return false;
        $this->oauthStatus->setOAuthVars([
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'token_expires' => $accessToken->getExpires()
        ], $setCookie);
        $clientFactory = new ClientFactory($this->oauthStatus->getToken(), $this->config);
        $userModel = new User($clientFactory->getClient());
        $user = (object) $userModel->getUser();
        $this->userStatus->setSigninID($user->id, $setCookie);
        return false;
    }

    public function getProvider() {
        return $this->model->getProvider();
    }

    public function signout() {
        $this->oauthStatus->setOAuthVars([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires' => null
        ]);
        $this->userStatus->setSigninID(null);
        return true;
    }
}
