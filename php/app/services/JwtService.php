<?php


//----------------------------------------------
//FILE NAME:  JwtService.php gen for Servit Framework Service
//DATE: 2019-05-03(Fri)  07:48:13 

//----------------------------------------------
use \Servit\Restsrv\RestServer\RestException as TestException;
use \Servit\Restsrv\Service\BaseService as BaseService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;


class JwtService extends BaseService
{


    private static $member = null;
    public static  function  getToken($user = null)
    {
        if (!$user) return [];
        $privateKeyFile = "private.key";
        $publicKeyFile = "public.key";
        $header = [
            "alg"   => "RS256",
            "typ"   => "JWT"
        ];
        $payload = [
            "iss"       => $user->token,
            "sub"       => "Lucky User",
            'aud'       => $user->username,
            "name"      => $user->name . ' ' . $user->lname,
            "roles"     => $user->roles,
            "uid"       => $user->id
        ];
        // $verify = verifyJWT('sha256', $jwt, $publicKeyFile);
        $jwt = self::generateJWT('sha256', $header, $payload, $privateKeyFile);
        return $jwt;
        // var_dump($jwt); // string(277) "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.R-41ycm1V7Kvx_Lnw6nha6OAFQ-vYvdhAdgqa1Ugkj17X4dpSWSO0KRCmnq7yd6ZM-RLEMY3PEXyUAs4F1XtomT6M-CziCpIB5piLfYHLG6V1_FrtieuIOMGLZGs-PpqMZX-JgJf_L19Ly9jnqGjfl9zo6BTTandhgNECE7AVk0"
        // $verify = verifyJWT('sha256', $jwt, $publicKeyFile);
        // var_dump($verify);
    }

    public static function verify($jwt)
    {
        $str_jwt = (string)$jwt;
        if ($str_jwt) {
            $publicKeyFile = "public.key";
            $verify = self::verifyJWT('sha256', $str_jwt, $publicKeyFile);
            if ($verify) {
                return self::$member;
            } else {
                return $verify;
            }
        } else {
            return false;
        }
    }

    private static function base64UrlEncode($data)
    {
        $data = (string)$data;
        $urlSafeData = strtr(base64_encode($data), '+/', '-_');
        return rtrim($urlSafeData, '=');
    }

    private static function base64UrlDecode($data)
    {
        $urlUnsafeData = strtr($data, '-_', '+/');
        $paddedData = str_pad($urlUnsafeData, strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($paddedData);
    }

    private static function getOpenSSLErrors()
    {
        $messages = [];
        while ($msg = openssl_error_string()) {
            $messages[] = $msg;
        }
        return $messages;
    }

    public static function generateJWT($algo, $header, $payload, $privateKeyFile)
    {
        $str_header = json_encode($header);
        $str_payload = json_encode($payload);
        $headerEncoded = self::base64UrlEncode($str_header);
        $payloadEncoded = self::base64UrlEncode($str_payload);
        $dataEncoded = "$headerEncoded.$payloadEncoded";
        $privateKey = "file://" . SRVPATH . '/configs/' . $privateKeyFile;
        $privateKeyResource = openssl_pkey_get_private($privateKey);
        $result = openssl_sign($dataEncoded, $signature, $privateKeyResource, $algo);
        if ($result === false) {
            throw new RuntimeException("Failed to generate signature: " . implode("\n", self::getOpenSSLErrors()));
        }
        $signatureEncoded = self::base64UrlEncode($signature);
        $jwt  = "$dataEncoded.$signatureEncoded";
        $pubpath = SRVPATH . '/configs/public.key';
        $pubkey = file_get_contents($pubpath);
        return [
            'token' => $jwt,
            'pubkey' => $pubkey,
        ];
    }

    private static function verifyJWT($algo,  $jwt,  $publicKeyFile)
    {
        if (empty($jwt)) return false;
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
        $dataEncoded  = "$headerEncoded.$payloadEncoded";
        $signature = self::base64UrlDecode($signatureEncoded);
        $privateKey = "file://" . SRVPATH . '/configs/' . $publicKeyFile;
        $publicKeyResource = openssl_pkey_get_public($privateKey);
        $result = openssl_verify($dataEncoded, $signature, $publicKeyResource, $algo);
        if ($result === -1) {
            throw new RuntimeException("Failed to verify signature: " . implode("\n", self::getOpenSSLErrors()));
        } else {
            $data = self::base64UrlDecode($payloadEncoded);
            $data = json_decode($data);
            $member = Member::find($data->uid);
            if ($data->iss == $member->token) {
                self::$member = $member;
                return true;
            } else {
                return false;
            }
        }
    }

    private static function jwtdata(string $algo, string $jwt, string $publicKeyFile)
    {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
        $data = self::base64UrlDecode($payloadEncoded);
        $data = json_decode($data);
        $member = Member::find($data->uid);
        return  $member;
    }
}