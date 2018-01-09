<?php
namespace Solleer\C2Logbook;
use Solleer\User\{SigninStatus};

class OAuthSignin {
    private $model;
    private $user;
    private $userStatus;
    private $oauthStatus;

    public function __construct(Authentication $model, User $user, SigninStatus $userStatus, OAuthStatus $oauthStatus) {
        $this->model = $model;
        $this->user = $user;
        $this->userStatus = $userStatus;
        $this->oauthStatus = $oauthStatus;
    }

    public function signin($code) {
        $accessToken = $this->model->getAccessToken($code);
        $user = (object) $this->user->getUser();
        $this->oauthStatus->setOAuthVars([
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'token_expires' => $accessToken->getExpires()
        ]);
        $this->userStatus->setSigninID($user->id);
        else return false;
    }

    public function signout() {
        $this->oauthStatus->setOAuthVars(null);
        $this->userStatus->setSigninID(null);
        return true;
    }
}
