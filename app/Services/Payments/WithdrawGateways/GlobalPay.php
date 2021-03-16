<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use App\Exceptions\UnsupportedTypeException;
use App\Exceptions\WithdrawException;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use App\Lib\Curl\Curl;
use http\Exception\UnexpectedValueException;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use App\Constants\Payments\ResponseCode;
use App\Exceptions\InputException;
use App\Constants\Payments\Status;

class GlobalPay extends AbstractWithdrawGateway
{
    // ================ 下單參數 ==================
    // 下單domain
    protected $domain = 'wrysc.orfeyt.com';
    // 下單網址
    protected $createSegments = '/withdraw/singleOrder';
    protected $createResultMessagePosition = 'err_msg';
    // 設定下單sign
    protected $createSign;
    protected $isCurlProxy = true;
    // ================ 回調參數 ==================
    // 停止callback回應的訊息
    protected $callbackSuccessReturnString = 'SUCCESS';
    // 回調的orderId位置
    protected $callbackOrderIdPosition = 'mer_order_no';
    // 回調的狀態位置
    protected $callbackOrderStatusPosition = 'status';
    protected $callbackOrderAmountPosition = 'order_amount';
    protected $callbackOrderMessagePosition = 'err_msg';
    // 回調成功狀態
    protected $callbackSuccessStatus = ['SUCCESS'];
    // 回調確認失敗狀態
    protected $callbackFailedStatus = ['FAIL'];

    public function __construct(Curl $curl) {
        parent::__construct($curl);
    }

    public function setRequest($post = [], WithdrawOrder $order) : void {
        $this->setBaseRequest($order, $post);
    }

    protected function validationCreateInput() {
        return [
            'order_id'         => 'required',
            'withdraw_address' => 'required',
            'first_name'       => 'required',
            'last_name'        => 'required',
            'amount'           => 'required',

        ];
    }


    protected function setCreateSign($post, $settings) {
        $signParams = $this->getNeedGenSignArray($post,  $settings);
        $this->createSign =  $this->genSign($signParams, $settings);
    }

    private function getNeedGenSignArray($input, $settings) {
        $this->setCallBackUrl(__CLASS__);
        if (empty($settings['merchant_number']) || empty($settings['md5_key'])) {
            throw new InputException('merchant_username or md5_key key not found', Status::ORDER_FAILED);
        }
        $array = [];
        $array['mer_no']       = $settings['merchant_number'];
        $array['mer_order_no'] = $input['order_id'];
        $array['acc_no']       = $input['withdraw_address'];
        $array['acc_name']     = $input['first_name'] . $input['last_name'];
        $array['ccy_no']       = 'INR';
        $array['order_amount'] = (float) $input['amount'];
        $array['bank_code']    = $this->getBankCodeOrUpiCode($input, $settings);
        $array['province']     = $input['ifsc']??'';
        $array['notifyUrl']    = $this->callbackUrl;
        // $array['notifyUrl']    = 'http://admin02.6122028.com'. '/callback/withdraw/GlobalPay';
        $array['summary']      = 'aa';
        return $array;

    }

    private function getBankCodeOrUpiCode($input, $settings) {
        if ($input['type'] == Type::BANK_CARD) {
            return $input['transaction_type'];
        }

        if ($input['type'] == Type::WALLET) {
            return $settings['transaction_type'];
        }
    }

    protected function genSign($postData, $settings) {
        ksort($postData);
        $str = '';
        foreach($postData as $key => $value) {
            if ($value == '') {continue;}
            $str .= $key . '=' . $value . '&';
        }
        $str.='key=' . $settings['md5_key'];

        return md5($str);
    }

    protected function setCreatePostData($post, $settings) {
        $array = $this->getNeedGenSignArray($post,  $settings);
        $array['sign'] = $this->createSign;
        $this->createPostData = json_encode($array);
    }

    protected function isHttps() {
        return false;
    }

    protected function getCurlHeader() {
        return [
            'Content-Type: application/json',
            "HOST: ". $this->domain,
        ];
    }

    protected function checkCreateOrderIsSuccess($res) {
        return isset($res['status']) && $res['status'] == 'SUCCESS';
    }

    // ===========================callback start===============================

    protected function getCallbackSign(Request $request) {
        $post = $request->post();
        return $post['sign'];
    }

    protected function genCallbackSign($postJson, $settings) {
        $post = $this->decode($postJson);
        unset($post['sign']);
        return $this->genSign($post, $settings);
    }

    public function  getCallBackInput(Request $request) {
        # 用php://input amount 小數點不會被削掉, request->post()會 ex:10.0000 => 10
        return  json_encode($request->post());
    }

    protected function getCallbackValidateColumns() {
        return [
            'order_no' => 'required',
            'sign'     => 'required',
            'status'   => 'required',
        ];
    }

    // ======================= 下拉提示 ===========================

    public function getPlaceholder($type):Placeholder
    {
        switch ($type) {
            case Type::WALLET:
                $transactionType = ['UPI', 'Paytm'];
                break;

            case Type::BANK_CARD:
                $transactionType = ['ydEbank'];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            'Please input MerchantID',
            '',
            '',
            'Please input MD5 Key',
            '',
            '',
            $transactionType,
        );
    }


    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        switch ($type) {
            case Type::BANK_CARD:
                $column = [
                    C::AMOUNT,
                    C::BANK,
                    C::IFSC,
                    C::BANK_ACCOUNT,
                    C::FIRST_NAME,
                    C::LAST_NAME,
                    C::FUND_PASSWORD
                ];
                break;

            case Type::WALLET:
                    $column = [
                        C::AMOUNT,
                        C::UPI_ID,
                        C::BANK_ACCOUNT,
                        C::FIRST_NAME,
                        C::LAST_NAME,
                        C::FUND_PASSWORD
                    ];
                    break;
            default:
                throw new UnsupportedTypeException();
                break;
        }

        $bank = [
            [
                'id' => 'IDPT0001',
                'name'=>'IDPT0001	Canara Bank 卡纳拉银行'
            ],
            [
                'id' => 'IDPT0002',
                'name'=>'DCB Bank DCB銀行'
            ],
            [
                'id' => 'IDPT0003',
                'name'=>'Federal Bank 聯邦銀行'
            ],
            [
                'id' => 'IDPT0004',
                'name'=>'DFC Bank  HDFC银行'
            ],
            [
                'id' => 'IDPT0005',
                'name'=>'unjab National Bank 旁遮普國家銀行'
            ],
            [
                'id' => 'IDPT0006',
                'name'=>'Indian Bank 印度银行'
            ],
            [
                'id' => 'IDPT0007',
                'name'=>'ICICI Bank 印度工业信贷投资银行'
            ],
            [
                'id' => 'IDPT0008',
                'name'=>'Syndicate Bank 辛迪加銀行'
            ],
            [
                'id' => 'IDPT0009',
                'name'=>'Karur Vysya Bank 卡魯爾Vysya銀行'
            ],
            [
                'id' => 'IDPT0010',
                'name'=>'Union Bank of India 印度联合银行'
            ],
            [
                'id' => 'IDPT0011',
                'name'=>'Kotak Mahindra Bank 科塔克马辛德拉银行'
            ],
            [
                'id' => 'IDPT0012',
                'name'=>'IDFC First Bank  IDFC第一銀行'
            ],
            [
                'id' => 'IDPT0013',
                'name'=>'Andhra Bank 安德拉銀行'
            ],
            [
                'id' => 'IDPT0014',
                'name'=>'Karnataka Bank 卡纳塔克邦银行'
            ],
            [
                'id' => 'IDPT0015',
                'name'=>'cici corporate bank 印度工业信贷投资银行 (公户)'
            ],
            [
                'id' => 'IDPT0016',
                'name'=>'Axis Bank 艾克塞斯银行'
            ],
            [
                'id' => 'IDPT0017',
                'name'=>'UCO Bank UCO銀行'
            ],
            [
                'id' => 'IDPT0018',
                'name'=>'South Indian Bank 南印度銀行'
            ],
            [
                'id' => 'IDPT0019',
                'name'=>'Yes Bank YES银行'
            ],
            [
                'id' => 'IDPT0020',
                'name'=>'Standard Chartered Bank 渣打银行'
            ],
            [
                'id' => 'IDPT0021',
                'name'=>'State Bank of India 印度国家银行'
            ],
            [
                'id' => 'IDPT0022',
                'name'=>'ndian Overseas Bank  印度海外銀行'
            ],
            [
                'id' => 'IDPT0023',
                'name'=>'Bandhan Bank 班丹銀行'
            ],
            [
                'id' => 'IDPT0024',
                'name'=>'Central Bank of India 印度中央银行'
            ],
        ];

        return new WithdrawRequireInfo($type, $column, [], $bank);
    }
}
