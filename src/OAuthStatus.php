<?php

namespace Solleer\C2Logbook;

class OAuthStatus {
    public function setOAuthVars(array $vars) {
        $_SESSION['oauth'] = $vars;
        return true;
    }

    public function getOAuthVars() {
        return $_SESSION['oauth'];
    }

    public function getToken() {
        return $this->getOAuthVars()['access_token'] ?? false;
    }
}
