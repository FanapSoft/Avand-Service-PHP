<?php
/**
 * Created by PhpStorm.
 * User :  keshtgar
 * Date :  6/19/19
 * Time : 12:29 PM
 *
 * $baseInfo BaseInfo
 */
namespace Avand\Service;
use Pod\Base\Service\ApiRequestHandler;
use Pod\Base\Service\BaseService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\PodException;
use Avand\Service\AvandApiRequestHandler;

class AvandService extends BaseService
{
    private $header;
    private static $jsonSchema;
    private static $avandApi;
    private static $serviceCallProductId;
    private static $baseUri;

    public function __construct($baseInfo)
    {
        BaseInfo::initServerType(BaseInfo::PRODUCTION_SERVER);
        parent::__construct();
        self::$jsonSchema = json_decode(file_get_contents(__DIR__ . '/../config/validationSchema.json'), true);
        $this->header = [
            '_token_issuer_'    =>  $baseInfo->getTokenIssuer(),
            '_token_'           => $baseInfo->getToken()
        ];
        self::$avandApi = require __DIR__ . '/../config/apiConfig.php';
        self::$serviceCallProductId = require __DIR__ . '/../config/serviceProductId.php';
        self::$baseUri = self::$config[self::$serverType];
        self::$serviceCallProductId = self::$serviceCallProductId[self::$serverType];

    }

    public function issueInvoice($params) {
        $apiName = 'issueInvoice';
        list($method, $option, $optionHasArray) = $this->prepareDataBeforeSend($params, $apiName);

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$avandApi[$apiName]['baseUri']],
            $method,
            self::$avandApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray);
    }

    public function getInvoiceList($params) {
        $apiName = 'getInvoiceList';
        list($method, $option, $optionHasArray) = $this->prepareDataBeforeSend($params, $apiName);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$avandApi[$apiName]['baseUri']],
            $method,
            self::$avandApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray);
    }

    public function verifyInvoice($params) {
        $apiName = 'verifyInvoice';
        list($method, $option, $optionHasArray) = $this->prepareDataBeforeSend($params, $apiName);

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$avandApi[$apiName]['baseUri']],
            $method,
            self::$avandApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function cancelInvoice($params) {
        $apiName = 'cancelInvoice';
        list($method, $option, $optionHasArray) = $this->prepareDataBeforeSend($params, $apiName);

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$avandApi[$apiName]['baseUri']],
            $method,
            self::$avandApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    private function prepareDataBeforeSend($params, $apiName){
        $header = $this->header;
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$avandApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        // if token is set replace it
        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])  || isset($params['issuerId'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return [$method, $option, $optionHasArray];
    }

}