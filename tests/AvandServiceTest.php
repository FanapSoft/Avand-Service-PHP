<?php
/**
 * Created by PhpStorm.
 * User: keshtgar
 * Date: 11/11/19
 * Time: 9:49 AM
 */
use PHPUnit\Framework\TestCase;
use Avand\Service\AvandService;
use Pod\Common\Service\CommonService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class AvandServiceTest extends TestCase
{
//    public static $apiToken;
    public static $avandService;
    public static $commonService;
    const TOKEN_ISSUER = 1;
    const API_TOKEN = '{Put Api Token}';
    const ACCESS_TOKEN = '{Put Access Token}';
    const CLIENT_ID = '{Put client Id}';
    const CLIENT_SECRET = '{Put client secret}';
    const CONFIRM_CODE = '{Put Confirm Code}';
    private $tokenIssuer;
    private $token;
    private $scApiKey;

    public function setUp(): void
    {
        parent::setUp();
        # set serverType to SandBox or Production
        BaseInfo::initServerType(BaseInfo::PRODUCTION_SERVER);
        $avandTestData = require __DIR__ . '/avandTestDataLocal.php';
        $this->token = $avandTestData['token'];
        $this->tokenIssuer =  $avandTestData['tokenIssuer'];
        $this->scApiKey = $avandTestData['scApiKey'];

        $baseInfo = new BaseInfo();
        $baseInfo->setTokenIssuer($this->tokenIssuer);
        $baseInfo->setToken($this->token);
        self::$avandService = new AvandService($baseInfo);
    }

    public function testIssueInvoiceAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'redirectUri' => 'http://www.google.com',
                'userId' => 2127611,
                'businessId' => 4826,
                'price' => 1000,
                ## =========================== Optional Parameters  ===========================
                'scApiKey'       => $this->scApiKey,
                'scVoucherHash'  => ['123'],
                'token'          => $this->token,
            ];

        try {
            $result = self::$avandService->issueInvoice($params);
            $this->assertFalse($result['hasError']);
            $this->assertEquals(0, $result['result']['result']['errorCode']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueInvoiceRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'redirectUri' => 'http://www.google.com',
                'userId' => 2127611,
                'businessId' => 4826,
                'price' => 1000,
            ];
        try {
            $result = self::$avandService->issueInvoice($params);
            $this->assertFalse($result['hasError']);
            $this->assertEquals(0, $result['result']['result']['errorCode']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        $paramsWrongValue = [
            ## ============================ *Required Parameters  =========================
            'redirectUri' => 'wwwgoogle.com',
            'userId' => '2127611',
            'businessId' => '4826',
            'price' => '1000',
            ## =========================== Optional Parameters  ===========================
            'scApiKey'       => 1234,
            'scVoucherHash'  => '1234',
            'token'          => 1234,
        ];
        try {
            self::$avandService->issueInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('redirectUri', $validation);
            $this->assertEquals('The property redirectUri is required', $validation['redirectUri'][0]);

            $this->assertArrayHasKey('userId', $validation);
            $this->assertEquals('The property userId is required', $validation['userId'][0]);

            $this->assertArrayHasKey('businessId', $validation);
            $this->assertEquals('The property businessId is required', $validation['businessId'][0]);

            $this->assertArrayHasKey('price', $validation);
            $this->assertEquals('The property price is required', $validation['price'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        try {
            self::$avandService->issueInvoice($paramsWrongValue);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('redirectUri', $validation);
            $this->assertEquals('Invalid URL format', $validation['redirectUri'][1]);

            $this->assertArrayHasKey('userId', $validation);
            $this->assertEquals('String value found, but an integer is required', $validation['userId'][1]);

            $this->assertArrayHasKey('businessId', $validation);
            $this->assertEquals('String value found, but an integer is required', $validation['businessId'][1]);

            $this->assertArrayHasKey('price', $validation);
            $this->assertEquals('String value found, but a number is required', $validation['price'][1]);

            $this->assertArrayHasKey('_token_', $validation);
            $this->assertEquals('Integer value found, but a string is required', $validation['_token_'][0]);

            $this->assertArrayHasKey('scApiKey', $validation);
            $this->assertEquals('Integer value found, but a string is required', $validation['scApiKey'][0]);

            $this->assertArrayHasKey('scVoucherHash', $validation);
            $this->assertEquals('String value found, but an array is required', $validation['scVoucherHash'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListAllParameters()
    {
        $params1 =
            [
                ## ============================ *Required Parameters one of  =========================
                'offset' => 0,
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
//                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
//                'scApiKey'           => '{Put service call Api Key}',
            ];
        try {
            $result = self::$avandService->getInvoiceList($params1);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    $params2 =
            [
                ## ============================ *Required Parameters one of  =========================
                'firstId' => 1, # در صورتی که این فیلد وارد شود فیلدهای lastId و offset نباید وارد شوند و نتیجه صعودی مرتب می شود.
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$avandService->getInvoiceList($params2);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    $params3 =
            [
                ## ============================ *Required Parameters one of  =========================
                'lastId' => 1000000, # در صورتی که این فیلد وارد شود فیلدهای firstId و offset نباید وارد شوند و نتیجه نزولی مرتب می شود.
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$avandService->getInvoiceList($params3);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListRequiredParameters()
    {
        $reqParam1 =
            [
                ## ============================ *Required Parameters one of  =========================
                'offset' => 0, # در صورتی که این فیلد وارد شود فیلدهای lastId و firstId نباید وارد شوند و نتیجه نزولی مرتب می شود
            ];
        try {
            $result = self::$avandService->getInvoiceList($reqParam1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam2 =
            [
                ## ============================ *Required Parameters one of  =========================
                'firstId' => 1, # در صورتی که این فیلد وارد شود فیلدهای lastId و offset نباید وارد شوند و نتیجه صعودی مرتب می شود.
            ];
        try {
            $result = self::$avandService->getInvoiceList($reqParam2);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam3 =
            [
                ## ============================ *Required Parameters one of  =========================
                'lastId' => 10000, # در صورتی که این فیلد وارد شود فیلدهای firstId و offset نباید وارد شوند و نتیجه نزولی مرتب می شود
            ];
        try {
            $result = self::$avandService->getInvoiceList($reqParam3);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$avandService->getInvoiceList($paramsWithoutRequired);
        } catch (ValidationException $e) {
            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('lastId', $validation);
            $this->assertEquals('The property lastId is required', $validation['lastId'][0]);
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);
            $this->assertArrayHasKey('firstId', $validation);
            $this->assertEquals('The property firstId is required', $validation['firstId'][0]);
            $this->assertArrayHasKey('oneOf', $validation);
            $this->assertEquals('Failed to match exactly one schema', $validation['oneOf'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testVerifyInvoiceAllParameters()
    {
        $params1 =
        [
        ## ====================  Optional Parameters  =====================
            'invoiceId' => 9114246,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$avandService->verifyInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('امکان تایید کردن فاکتور نیست.' , $error['message']);
        }
    }

    public function testVerifyInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'invoiceId' => 9114246,
            ];

        try {
            $result = self::$avandService->verifyInvoice($params1);
            $this->assertTrue($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('امکان تایید کردن فاکتور نیست.' , $error['message']);
        }
    }

    public function testVerifyInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$avandService->verifyInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testCancelInvoiceAllParameters()
    {
        $params1 =
        [
            ## ============================ *Required Parameters  =========================
            'invoiceId' => 9114246,
            ## ====================  Optional Parameters  =====================
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$avandService->cancelInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('امکان کنسل کردن فاکتور نیست.' , $error['message']);
        }
    }

    public function testCancelInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'invoiceId' => 9114246,
            ];

        try {
            $result = self::$avandService->cancelInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('امکان کنسل کردن فاکتور نیست.' , $error['message']);
        }
    }

    public function testCancelInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$avandService->cancelInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

}