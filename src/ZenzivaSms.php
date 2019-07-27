<?php
/**
 * This file is part of the Zenziva Sms Package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace Steevenz;

// ------------------------------------------------------------------------

use O2System\Curl;
use O2System\Kernel\Http\Message\Uri;
use O2System\Spl\DataStructures\SplArrayObject;
use O2System\Spl\Traits\Collectors\ConfigCollectorTrait;
use O2System\Spl\Traits\Collectors\ErrorCollectorTrait;

/**
 * Class ZenzivaSms
 */
class ZenzivaSms
{
    use ConfigCollectorTrait;
    use ErrorCollectorTrait;

    /**
     * ZenzivaSms::$deliveryStatuses
     *
     * List of Zenziva Sms code status by code numbers.
     *
     * @var array
     */
    public $statusCodes = [
        0  => 'Success',
        1  => 'Nomor tujuan tidak valid',
        5  => 'Userkey / Passkey salah',
        6  => 'Konten SMS rejected',
        89 => 'Pengiriman SMS berulang-ulang ke satu nomor dalam satu waktu',
        99 => 'Credit tidak mencukupi',
    ];

    /**
     * ZenzivaSms::$response
     *
     * Zenziva Sms original response.
     *
     * @access  protected
     * @type    mixed
     */
    protected $response;

    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::__construct
     *
     * @param array $config
     *
     * @access  public
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'apiUrl'  => 'https://reguler.zenziva.net/apps/',
            'userkey' => null,
            'passkey' => null,
        ];

        $this->setConfig(array_merge($defaultConfig, $config));
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::setApiUrl
     *
     * Set Zenziva Sms API Url.
     *
     * @param string $serverIp Zenziva Sms API Url.
     *
     * @access  public
     * @return  static
     */
    public function setApiUrl($apiUrl)
    {
        $this->setConfig('apiUrl', $serverIp);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::setUserkey
     *
     * Set Zenziva Sms Userkey.
     *
     * @param string $userkey Zenziva Sms Userkey
     *
     * @access  public
     * @return  static
     */
    public function setUserkey($userkey)
    {
        $this->setConfig('userkey', $userkey);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::setPasskey
     *
     * Set Zenziva Sms Passkey.
     *
     * @param string $userkey Zenziva Sms Passkey
     *
     * @access  public
     * @return  static
     */
    public function setPasskey($passkey)
    {
        $this->setConfig('passkey', $userkey);

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::request
     *
     * Call API request.
     *
     * @param string $path
     * @param array  $params
     * @param string $type
     *
     * @access  protected
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    protected function request($path, $params = [], $type = 'GET')
    {
        // default params
        if (empty($this->config[ 'apiUrl' ])) {
            throw new \InvalidArgumentException('Zenziva Sms: API Url is not set!');
        }

        if (empty($this->config[ 'userkey' ])) {
            throw new \InvalidArgumentException('Zenziva Sms: Userkey is not set');
        } else {
            $defaultParams[ 'userkey' ] = $this->config[ 'userkey' ];
        }

        if (empty($this->config[ 'passkey' ])) {
            throw new \InvalidArgumentException('Zenziva Sms: Passkey is not set');
        } else {
            $defaultParams[ 'passkey' ] = $this->config[ 'passkey' ];
        }

        $uri = (new Uri($this->config[ 'apiUrl' ]))->withPath($path);
        $request = new Curl\Request();
        $request->setConnectionTimeout(500);

        if ($this->response = $request->setUri($uri)->get(array_merge($defaultParams, $params))) {
            if (false !== ($error = $this->response->getError())) {
                $this->addError($error->code, $error->message);
            } elseif ($body = $this->response->getBody()) {
                if (isset($body->message->status)) {
                    if ($body->message->status == 0) {
                        return new SplArrayObject([
                            'id'      => $body->message->messageId,
                            'status'  => $body->message->status,
                            'message' => $body->message->text,
                            'balance' => $body->message->balance,
                        ]);
                    } else {
                        $this->addError($body->message->status, $body->message->text);
                    }
                }
            }
        }

        return false;
    }
    // ------------------------------------------------------------------------

    /**
     * Rajasms::buildSendPackageData
     *
     * @param array $data
     *
     * @return array|bool
     */
    protected function validateMsisdn($msisdn)
    {
        if (preg_match('/^(62[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            $msisdn = '0' . substr($msisdn, 2);
        } elseif (preg_match('/^(\+62[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            $msisdn = '0' . substr($msisdn, 3);
        }

        if (preg_match('/^(0[1-9]{1}[0-9]{1,2})[0-9]{6,8}$/', $msisdn) == 1) {
            return trim($msisdn);
        }

        return false;
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::send
     *
     * Send SMS
     *
     * @param string $msisdn  MSISDN Number
     * @param string $message Message
     *
     * @access  public
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function send($msisdn, $message)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Zenziva Sms: Invalid MSISDN Number');
        }

        return $this->request('apps/smsapi.php', [
            'nohp'  => $msisdn,
            'pesan' => $message,
        ]);
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::send
     *
     * Send SMS
     *
     * @param string $msisdn  MSISDN Number
     * @param string $otpCode Otp Code
     *
     * @access  public
     * @return  mixed
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function sendOtp($msisdn, $otpCode)
    {
        if (false === ($msisdn = $this->validateMsisdn($msisdn))) {
            throw new \InvalidArgumentException('Zenziva Sms: Invalid MSISDN Number');
        }

        $otpCode = trim($otpCode);

        if (strlen($otpCode) < 4) {
            throw new \InvalidArgumentException('Zenziva Sms: OTP Code minimum length is 4 digit');
        } elseif (strlen($otpCode) > 8) {
            throw new \InvalidArgumentException('Zenziva Sms: OTP Code maximum length is 8 digit');
        }

        return $this->request('apps/smsotp.php', [
            'nohp'     => $msisdn,
            'kode_otp' => $otpCode,
        ]);
    }
    // ------------------------------------------------------------------------

    /**
     * ZenzivaSms::getResponse
     *
     * Get original response object.
     *
     * @access  public
     * @return  \O2System\Curl\Response|bool Returns FALSE if failed.
     */
    public function getResponse()
    {
        return $this->response;
    }
}