<?php
namespace Solleer\C2Logbook\Authorize;
use Solleer\C2Logbook\{OAuthStatus, Authentication, OAuthSignin};
class User implements \Solleer\User\Authorizable {
    private $auth;
    private $signin;
    private $status;

    public function __construct(Authentication $auth, OAuthSignin $signin, OAuthStatus $status) {
        $this->auth = $auth;
        $this->signin = $signin;
        $this->status = $status;
    }

    public function authorize($user, array $args): bool {
        //if (empty($user)) return false;

        $status = $this->status->getOAuthVars();
        if (empty($status['token_expires'])) return false;

        if (time() > $status['token_expires']) {
            if (!$status['refresh_token']) {
                $this->signin->signout();
                return false;
            }

            $accessToken = $this->auth->refreshToken($status['refresh_token']);

            if (!$accessToken) {
                $this->signin->signout();
                return false;
            }

            $this->status->setOAuthVars([
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken() ?? false,
                'token_expires' => $accessToken->getExpires()
            ]);
        }

        return true;
    }
}
