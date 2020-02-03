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
use Pod\Base\Service\BaseService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\PodException;
use Avand\Service\AvandApiRequestHandler;

class AvandService extends BaseService
{
    private $header;
    private static $jsonSchema;
    private static $billingApi;
    private static $serviceProductId;
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
        self::$billingApi = require __DIR__ . '/../config/apiConfig.php';
        self::$serviceProductId = require __DIR__ . '/../config/serviceProductId.php';
        self::$baseUri = self::$config[self::$serverType];
        self::$serviceProductId = self::$serviceProductId[self::$serverType];

    }

    public function issueInvoice($params) {
        $apiName = 'issueInvoice';
        $optionHasArray = false;
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray);
    }

    public function getInvoiceList($params) {
        $apiName = 'getInvoiceList';
        $optionHasArray = false;
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash']) || isset($params['issuerId'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray);
    }

    public function verifyInvoice($params) {
        $apiName = 'verifyInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function cancelInvoice($params) {
        $apiName = 'cancelInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return AvandApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

}