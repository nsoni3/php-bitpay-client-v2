<?php

namespace BitPaySDK\Exceptions;


class WalletException extends BitPayException
{
    private $bitPayMessage = "An unexpected error occurred while trying to manage the wallet";
    private $bitPayCode    = "BITPAY-WALLET-GENERIC";
    protected $apiCode;

    /**
     * Construct the WalletException.
     *
     * @param string $message [optional] The Exception message to throw.
     * @param int    $code    [optional] The Exception code to throw.
     * @param string $apiCode [optional] The API Exception code to throw.
     */
    public function __construct($message = "", $code = 181, Exception $previous=NULL, $apiCode = "000000")
    {
        if (!$message) {
            $message = $this->bitPayCode.": ".$this->bitPayMessage."-> ".$message;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string Error code provided by the BitPay REST API
     */
    public function getApiCode()
    {
        return $this->apiCode;
    }
}
