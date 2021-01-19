<?php


namespace YunInternet\WHMCS\WeChatPay\Tests\Unit;


use PHPUnit\Framework\TestCase;
use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\CertificateGetters\MockCertificateGetter;
use YunInternet\WHMCS\WeChatPay\CertificateManager;
use YunInternet\WHMCS\WeChatPay\CertificateRepositories\HashTableCertificateRepository;

class CertificateManagerTest extends TestCase
{
    public function testGetCertificates()
    {
        $certificates = [
            new Certificate("5490dc443153d56de5eba0a1e16b2622ccdefa50", "-----BEGIN CERTIFICATE-----
MIIDZTCCAk2gAwIBAgIUVJDcRDFT1W3l66Ch4WsmIsze+lAwDQYJKoZIhvcNAQEL
BQAwQjELMAkGA1UEBhMCWFgxFTATBgNVBAcMDERlZmF1bHQgQ2l0eTEcMBoGA1UE
CgwTRGVmYXVsdCBDb21wYW55IEx0ZDAeFw0yMTAxMTgxNDMxNTlaFw0yMjAxMTgx
NDMxNTlaMEIxCzAJBgNVBAYTAlhYMRUwEwYDVQQHDAxEZWZhdWx0IENpdHkxHDAa
BgNVBAoME0RlZmF1bHQgQ29tcGFueSBMdGQwggEiMA0GCSqGSIb3DQEBAQUAA4IB
DwAwggEKAoIBAQDg9xcqIMBwjwFn4A8H0Udsc2qmF1OXYKvXdaYlAzFPAFAjauxE
xKO9nYRSNvUi0M9Xlj2dxQaaQX8pUvcsAO7EQklVdZGPdqoNv5kZNt26Seeh4elk
APHyDn17KP1Q785MoWWPET/nQLLJcW3RhXAkNo3TDqDyFSZB+zt41EQWvy4d/qFX
zOqD9SJexXwc3Rv3eNK+IGNGp4ckmMPjmHeLUfcikkrMRX7mIdE5//nNhr/b3m7S
6RqNs6e3XJlVeOY+VJq2kJgQ44l24B8xB5uwI34VZvZygv0E0bp5Ghg5RpSIflpi
8pyiMcqvfS9+0RTxLGGP2TvU/QbUMlvqJQwLAgMBAAGjUzBRMB0GA1UdDgQWBBQ0
tnDjlO2JveEHJwnw3Hf+dKXaNjAfBgNVHSMEGDAWgBQ0tnDjlO2JveEHJwnw3Hf+
dKXaNjAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQDEonZHKY1Z
6lmWsCITwuWyW165ZjflQUC0l2tBC/grOVRlMrfVJdomqRhTzS3M2meZ18ptnVuk
Z4rl796f8gzX5uBfi7tP/lPrHBuMVRIjZvMgxHxz9/7GjZ/1aNBPcHTZJ2pdrOwS
p7jQVKQIckSopcvTdl2qpYC8Jb2zQq8J/yIk0e5R7xsMXYNHBqnacc/+cvnKKCMO
LqrBPd8ule/NFvSmhbrWfuozNBekuO/1BZgRPT4WA2PlaElQki3YDkcZykx7KPBN
CuAkr/0MGpy9o+/RO1XNXsOaOasHpIbTAu2JkWRJ7MpCuXNQbY4yp0g4fIyl0RIG
h9B6McyRuhw1
-----END CERTIFICATE-----", "2021-01-18 22:31:59", "2022-01-18 22:31:59"),
            new Certificate("09efcf93d68a0a7fc1e52339252a454cbc33567d", "-----BEGIN CERTIFICATE-----
MIIDZTCCAk2gAwIBAgIUCe/Pk9aKCn/B5SM5JSpFTLwzVn0wDQYJKoZIhvcNAQEL
BQAwQjELMAkGA1UEBhMCWFgxFTATBgNVBAcMDERlZmF1bHQgQ2l0eTEcMBoGA1UE
CgwTRGVmYXVsdCBDb21wYW55IEx0ZDAeFw0yMTAxMTgxNDMzMzhaFw0yMjAxMTgx
NDMzMzhaMEIxCzAJBgNVBAYTAlhYMRUwEwYDVQQHDAxEZWZhdWx0IENpdHkxHDAa
BgNVBAoME0RlZmF1bHQgQ29tcGFueSBMdGQwggEiMA0GCSqGSIb3DQEBAQUAA4IB
DwAwggEKAoIBAQDfYhROyVo+TtWxW5hYQgo29RrwQM0QA2imodBhRgtiVWlnCb7d
zWWHOMS5WpKvhu7dnLNzvqmEQRmedCOO3Peh9JrgagcNpVVZysaggQEGurUaDh7G
6q/jZwI9yMcYmBXk56fEsD10lSvj9zR2Z33Ghn3P3TtxHQ8SldOA4A2epZ9lRlpJ
TIIieb1FKQOywbFzXi3acKah9nSauqaTFzRweSK7WXTWUpoQIWpUZzzAgJCW5riB
yaeaMbeLQ93oFaQog1qRXKdVPWIf1FoMMO9xUVSBqSGav8zh+rcHiKSTCzlqcTS7
HfWP4oe+UlKkp/veBb8kts6LOujH4/QI9R3xAgMBAAGjUzBRMB0GA1UdDgQWBBSR
FKaGgCe19pJuvpr3IuRVorDhATAfBgNVHSMEGDAWgBSRFKaGgCe19pJuvpr3IuRV
orDhATAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAcTYSXMc/T
N1dS088hz0MN58ONvSyRyFeIg+GRKCdX/oJJ9Y1VarnMBveOoIoE4jzGTlLI6mSa
PxNAjKBiIAk0+29MmnHQlWfeiPS0vhGKS3Q0hnWN5pFm4Y4Jx77WLFI+x+XLIdgD
D6y3QTI9VjuRl09qH52RdfHjs7oS0d3vJzkr35zDk2EF1rOAYtOLmOzkeC162T9i
Czuv8G3f/G2AV8824hKhegl5xsU2e+1VDTtN5Ator4yeRyVJ0iZbB7oUbiNBgE3j
iPtjEsDyjMCLujiGrCHsD8RIa8CGZHo1ZsFvDHa8i7dfu0cUjxhT6/Tgo6V3gXw2
dvNFQkE3Nb+y
-----END CERTIFICATE-----", "2021-01-18 22:33:38", "2022-01-18 22:33:38"),
        ];
        $mockCertificateGetter = new MockCertificateGetter($certificates);
        $certificateManager = new CertificateManager(new HashTableCertificateRepository(), $mockCertificateGetter);
        $this->assertEquals($certificates, $certificateManager->getAllCertificates());
        $this->assertEquals(1, $mockCertificateGetter->invokeCounter);

        $mockCertificateGetter = new MockCertificateGetter($certificates);
        $certificateManager = new CertificateManager(new HashTableCertificateRepository(), $mockCertificateGetter);
        $this->assertEquals($certificates[1], $certificateManager->getBySerialNo("09efcf93d68a0a7fc1e52339252a454cbc33567d"));
        $this->assertEquals(1, $mockCertificateGetter->invokeCounter);
    }
}