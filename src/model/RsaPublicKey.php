<?php

namespace org\desone\wordpress\wpLinkedData;

class RsaPublicKey {

    private $modulus;
    private $exponent;

    function __construct ($exponent, $modulus) {
        $this->exponent = $exponent;
        $this->modulus = $modulus;
    }

    public function getExponent () {
        return $this->exponent;
    }

    public function getModulus() {
        return $this->modulus;
    }

}
