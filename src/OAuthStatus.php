<?php

namespace Solleer\C2Logbook;

class OAuthStatus {
    public function setOAuthVars(array $vars, $setCookie = false) {
        if (isset($_COOKIE['oauth'])) $setCookie = true;
        $_SESSION['oauth'] = $vars;
        if ($setCookie) $this->setCookie('oauth', $vars);
        return true;
    }

    public function getOAuthVars() {
        return $_SESSION['oauth'] ?? $_COOKIE['oauth'] ?? '';
    }

    public function getToken() {
        return $this->getOAuthVars()['access_token'] ?? false;
    }

    private function setCookie($name, $value) {
        if (is_array($value)) {
            foreach ($value as $key => $val) $this->setCookie($name . "[$key]", $val);
            return;
        }

        if ($value) $expire = time()+(60*60*24*365);
        else $expire = 1;

        setCookie($name, $value ?? '', $expire, '/');
    }
}
