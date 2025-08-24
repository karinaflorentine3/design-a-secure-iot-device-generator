<?php
class SecureIoTDeviceGenerator {
    private $deviceName;
    private $deviceType;
    private $cryptoAlgorithm;
    private $deviceKey;

    function __construct($deviceName, $deviceType, $cryptoAlgorithm) {
        $this->deviceName = $deviceName;
        $this->deviceType = $deviceType;
        $this->cryptoAlgorithm = $cryptoAlgorithm;
        $this->deviceKey = $this->generateDeviceKey();
    }

    private function generateDeviceKey() {
        if ($this->cryptoAlgorithm == 'AES') {
            return openssl_random_pseudo_bytes(32, true);
        } elseif ($this->cryptoAlgorithm == 'RSA') {
            $privateKey = openssl_pkey_new();
            openssl_pkey_export($privateKey, $privateKeyPEM);
            $publicKey = openssl_pkey_get_details($privateKey);
            return array('private' => $privateKeyPEM, 'public' => $publicKey['key']);
        } else {
            throw new Exception('Unsupported crypto algorithm');
        }
    }

    public function generateDeviceConfig() {
        $deviceConfig = array(
            'device_name' => $this->deviceName,
            'device_type' => $this->deviceType,
            'crypto_algorithm' => $this->cryptoAlgorithm,
            'device_key' => $this->deviceKey
        );
        return json_encode($deviceConfig);
    }

    public function generateDeviceFirmware() {
        $firmwareCode = "<?php\n";
        $firmwareCode .= "define('DEVICE_NAME', '$this->deviceName');\n";
        $firmwareCode .= "define('DEVICE_TYPE', '$this->deviceType');\n";
        $firmwareCode .= "define('CRYPTO_ALGORITHM', '$this->cryptoAlgorithm');\n";
        $firmwareCode .= "define('DEVICE_KEY', '".base64_encode($this->deviceKey)."');\n";
        $firmwareCode .= "?>";
        return $firmwareCode;
    }
}

$generator = new SecureIoTDeviceGenerator('MySecureDevice', 'SmartLight', 'AES');
echo $generator->generateDeviceConfig();
echo $generator->generateDeviceFirmware();