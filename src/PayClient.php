<?php
namespace Payment\PaymentSdk;
use Illuminate\Database\Eloquent\Model;
/**支付网关客户端 */
class PayClient extends Model
{
    const HEADER_SEPARATOR = ';';
    /**
     * Some default options for curl
     * These are typically overridden by PayPalConnectionManager
     *
     * @var array
     */
    public static $defaultCurlOptions = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,    // maximum number of seconds to allow cURL functions to execute
        CURLOPT_USERAGENT => 'Gateway-SDK',
        CURLOPT_HTTPHEADER => array(),
    );

    private $curlOptions;

    private $headers = array();

    private $responseHeaders = array();

   /**
     * Default Constructor
     *
     * @param string $url
     * @param string $method HTTP method (GET, POST etc) defaults to POST
     * @param array $configs All Configurations
     */
    public function __construct()
    {
        $this->curlOptions = self::$defaultCurlOptions;
        $this->headers = ['Content-Type: application/json'];
    }

    /**
     * 发送请求
     *
     * @param [type] $url
     * @param [type] $method
     * @param [type] $data
     * @author wind <254044378@qq.com>
     */
    protected function executeCall($url, $method, $data)
    {   
        $url = $this->setPrefixUrl($url);
        $options = $this->getCurlOptions();
        $headers = $this->getHttpHeaders();
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $ch = curl_init($url);
        if (empty($options[CURLOPT_HTTPHEADER])) {
            unset($options[CURLOPT_HTTPHEADER]);
        }
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //Determine Curl Options based on Method
     
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'parseResponseHeaders'));

        //Execute Curl Request
        $result = curl_exec($ch);
        //Retrieve Response Status
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }

    /**
     * Parses the response headers for debugging.
     *
     * @param resource $ch
     * @param string $data
     * @return int
     */
    protected function parseResponseHeaders($ch, $data) {
        if (!$this->skippedHttpStatusLine) {
            $this->skippedHttpStatusLine = true;
            return strlen($data);
        }

        $trimmedData = trim($data);
        if (strlen($trimmedData) == 0) {
            return strlen($data);
        }

        // Added condition to ignore extra header which dont have colon ( : )
        if (strpos($trimmedData, ":") == false) {
            return strlen($data);
        }
        
        list($key, $value) = explode(":", $trimmedData, 2);

        $key = trim($key);
        $value = trim($value);

        // This will skip over the HTTP Status Line and any other lines
        // that don't look like header lines with values
        if (strlen($key) > 0 && strlen($value) > 0) {
            // This is actually a very basic way of looking at response headers
            // and may miss a few repeated headers with different (appended)
            // values but this should work for debugging purposes.
            $this->responseHeaders[$key] = $value;
        }

        return strlen($data);
    }

    /**
     * Gets all curl options
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }
    /**
     * Add Curl Option
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addCurlOption($name, $value)
    {
        $this->curlOptions[$name] = $value;
    }
    
    /**
     * 设置前缀
     *
     * @param [type] $url
     * @author wind <254044378@qq.com>
     */
    public function setPrefixUrl($url)
    {   
        $prefix = env('GATEWAY_URL', 'www.lumen.com');
        return $prefix . $url;
    }

    /**
     * Gets all Headers
     *
     * @return array
     */
    public function getHttpHeaders()
    {
        return $this->headers;
    }

    /**
     * Set Headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers = array())
    {
        $this->headers = $headers;
    }

    /**
     * Adds a Header
     *
     * @param      $name
     * @param      $value
     * @param bool $overWrite allows you to override header value
     */
    public function addHeader($name, $value, $overWrite = true)
    {
        if (!array_key_exists($name, $this->headers) || $overWrite) {
            $this->headers[$name] = $value;
        } else {
            $this->headers[$name] = $this->headers[$name] . self::HEADER_SEPARATOR . $value;
        }
    }
}   