<?php
namespace Solleer\C2Logbook\Controller;
use Solleer\C2Logbook\OAuthSignin as OAuthSigninModel;

class OAuthSignin {
    private $model;
    private $request;

    public function __construct(OAuthSigninModel $model, \Utils\Request $request) {
        $this->model = $model;
        $this->request = $request;
    }

    public function signin() {
        if (!empty($this->request->post())) $this->model->signin($this->request->post('code'), $this->request->post('setCookie'));
    }
}
