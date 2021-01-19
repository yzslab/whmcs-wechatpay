<?php


namespace YunInternet\WHMCS\WeChatPay\CertificateRepositories;

use \WHMCS\Database\Capsule;
use \Illuminate\Database\QueryException;
use YunInternet\WHMCS\WeChatPay\Certificate;
use YunInternet\WHMCS\WeChatPay\Contracts\CertificateRepository;
use YunInternet\WHMCS\WeChatPay\Exceptions\CertificateNotFoundException;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException;

class WHMCSDBCertificateRepository implements CertificateRepository
{
    const TABLE_NAME = "wechat_pay_certificates";

    /**
     * @inheritDoc
     */
    public function storeCertificates(array $certificates)
    {
        $this->createTable();
        try {
            $values = [];
            foreach ($certificates as $certificate) {
                $values[] = [
                    "serial_no" => $certificate->serialNo,
                    "certificate" => $certificate->certificate,
                    "effective_time" => $certificate->effectiveTime,
                    "expire_time" => $certificate->expireTime,
                ];
            }
            Capsule::connection()->transaction(
                function ($connectionManager) use ($values) {
                    $connectionManager->table(self::TABLE_NAME)->delete();
                    $connectionManager->table(self::TABLE_NAME)->insert($values);
                }
            );
        } catch (\Exception $e) {
            throw new WeChatPayException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getEffectiveCertificates(): array
    {
        $certificates = [];
        try {
            foreach (Capsule::table(self::TABLE_NAME)->where("expire_time", ">", date("Y-m-d H:i:s"))->get() as $certificate) {
                $certificates[] = new Certificate($certificate->serial_no, $certificate->certificate, $certificate->effective_time, $certificate->expire_time);
            }
        } catch (QueryException $e) {
            if ($e->getCode() !== "42S02") {
                throw $e;
            }
        }
        return $certificates;
    }

    public function getCertificate(string $serialNo): Certificate
    {
        $certificate = null;
        try {
            $certificate = Capsule::table(self::TABLE_NAME)->where("serial_no", $serialNo)->first();
        } catch (QueryException $e) {
            if ($e->getCode() !== "42S02") {
                throw $e;
            }
        }
        if (is_null($certificate)) {
            throw new CertificateNotFoundException();
        }
        return new Certificate($certificate->serial_no, $certificate->certificate, $certificate->effective_time, $certificate->expire_time);
    }

    private function createTable()
    {
        if (Capsule::schema()->hasTable(self::TABLE_NAME) === false) {
            Capsule::schema()->create(self::TABLE_NAME, function ($table) {
                $table->string('serial_no', 64)->primary();
                $table->text('certificate');
                $table->timestamp('effective_time')->nullable();
                $table->timestamp('expire_time')->nullable();
            });
        }
    }
}