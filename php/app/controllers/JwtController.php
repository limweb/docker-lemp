<?php

//----------------------------------------------
//FILE NAME:  JwtController.php gen for Servit Framework Controller
//DATE:                 2019-01-28(Mon)  00:10:23

//----------------------------------------------
use \Servit\Restsrv\RestServer\RestController as BaseController;

class JwtController extends BaseController
{

    public $member = null;

    public function authorize()
    {

        try {
            $token = $this->input->token;
            $jwt = new JwtService();
            $this->member = $jwt->verify($token);
            if($this->member){
                return true;
            } else {
                $this->server->setStatus(401);
                return false;
            }
        } catch (Exception $e) {
            $this->server->setStatus(401);
            return false;
        }
    }

}
