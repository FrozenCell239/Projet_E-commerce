<?php

namespace App\Service;

use DateTimeImmutable;
use Symfony\Bundle\MakerBundle\Str;

class JWTService{
    # Generating the JSON web token
    /**
     * Generating JWT web token
     * @param array $header
     * @param array $paylaod
     * @param string $secret
     * @param int $validity
     * @return string 
     */
    public function generate(
        array $header,
        array $paylaod,
        string $secret,
        int $validity = 10800
    ) : string
    {
        if($validity > 0){
            # Creating the payload
            $now = new DateTimeImmutable();
            $paylaod['iat'] = $now->getTimestamp();
            $paylaod['exp'] = $now->getTimestamp() + $validity; //Expiration
        };

        # Encoding to base 64
        $header = base64_encode(json_encode($header));
        $payload = base64_encode(json_encode($paylaod));

        # Cleaning encoded values (replacing +, /, and = caracters)
        $header = str_replace(['+', '/', '='], ['-', '_', ''], $header);
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

        # Generating signature
        $secret = base64_encode($secret);
        $signature = base64_encode(hash_hmac(
            'sha256',
            $header.'.'.$payload,
            $secret,
            true
        ));

        # Generating token
        $jwt = $header.'.'.$payload.'.'.$signature;

        return $jwt;
    }

    # Verifying token's validity and integrity
    public function isValid(string $token) : bool {
        return preg_match(
            '/^(?:[\w-]*\.){2}[\w-]*$/',
            $token
        ) === 1;
    }

    # Collecting header
    public function getHeader(string $token) : array {
        $array = explode('.', $token); //Dismounts the header.
        $header = json_decode(base64_decode($array[0]), true); //Decodes the header.
        return $header;
    }

    # Collecting payload
    public function getPayload(string $token) : array {
        $array = explode('.', $token); //Dismounts the payload.
        $paylaod = json_decode(base64_decode($array[1]), true); //Decodes the payload.
        return $paylaod;
    }

    # Verifying token's expiration
    public function isExpired(string $token) : bool {
        $paylaod = $this->getPayload($token);
        $now = new DateTimeImmutable();
        return $paylaod['exp'] < $now->getTimestamp();
    }

    # Checking token's signature
    public function check(string $token, string $secret){
        $header = $this->getHeader($token); //Collects header.
        $payload = $this->getPayload($token); //Collects payload.
        $verification_token = $this->generate($header, $payload, $secret, 0); //Generates verification token.
        return $token === $verification_token;
    }
}