<?php
/**
 * BtcTurk | PRO API PHP wrapper class
 * @author Onur Gozupek <onur@gozupek.com>
 * @web OnurGozupek.com <https://onurgozupek.com>
 * Modified by @CryptoYakari <https://twitter.com/CryptoYakari> Oct 2019
 * @web Robostopia.com <https://robostopia.com>
 */
 
class BtcTurkPRO
{
    private $baseUrl;
    private $apiKey;
    private $apiSecret;
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = 'https://api.btcturk.com/api/';
    }
    /**
     * Invoke API
     * @param string $method API method to call
     * @param array $params parameters
     * @param int $apiKey  use apikey (1) or not (0)
     * @param int $postMethod  get (0), post (1) or delete (3)
     * @return object
     */
    private function get_call($method, $params = array(), $apiKey = 0, $postMethod = 0)
    {
        $uri = $this->baseUrl.$method;
        if (!empty($params)) {
            if ($postMethod == 1) {
                $post_data = '{';
                foreach ($params as $key => $value)
                {
                    $post_data .= '"'.$key.'":';
                    if ($key == 'PairSymbol' || $key == 'newOrderClientId') {
                        $post_data .= '"'.$value.'"';
                    } else {
                        $post_data .= $value;
                    }
                    $post_data .= ', ';
                }
                $post_data = substr($post_data, 0, -2);
                $post_data .= '}';
            } else {
                $uri .= '?'.http_build_query($params);
            }
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, 'CURL_HTTP_VERSION_1_2');
            
        if ($postMethod == 1) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } elseif ($postMethod == 3) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        if ($apiKey == 1) {
            usleep(100000);
            $message = $this->apiKey.(time()*1000);
            $signatureBytes = hash_hmac('sha256', $message, base64_decode($this->apiSecret), true);
            $signature = base64_encode($signatureBytes);
            $nonce = time()*1000;
            $headers = array(
                'X-PCK: '.$this->apiKey,
                'X-Stamp: '.$nonce,
                'X-Signature: '.$signature,
                'Cache-Control: no-cache',
                'Content-Type: application/json',
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        $answer = json_decode($result);
        return $answer;
    }
 
	// API V1 Specific Functions

     /**
     * Retrieve your order history
     * @param string $type single parameter "buy" or array as object {"buy", "sell"}
     * @param string $symbol: single parameter for cryptocurrency or fiat "btc", "try" or array as object {"btc", "try", ...etc.}
     * @param string $startDate: Optional timestamp if null will return last 30 days
     * @param string $endDate: Optional timestamp if null will return last 30 days
     * @return object
     */
    public function UserTransactions($trntype = 'trade', $symbol = array("btc","eth","xrp","ltc","usdt","xlm","neo","try"), $type = array("buy", "sell"), $startDate = NULL, $endDate = NULL)
    {
        $params = array('type' => $type, 'symbol' => $symbol);
        if ($startDate) { $params['startDate'] = $startDate; }
        if ($endDate) { $params['endDate'] = $endDate; }
        return $this->get_call('v1/users/transactions/'.$trntype, $params, 1, 0);
    }
	
	public function UserTransfersCrypto($type = array('deposit', 'withdrawal'), $symbol = array("btc","eth","xrp","ltc","usdt","xlm","neo","try"), $startDate = NULL, $endDate = NULL)
    {
        $params = array('type' => $type, 'symbol' => $symbol);
        if ($startDate) { $params['startDate'] = $startDate; }
        if ($endDate) { $params['endDate'] = $endDate; }
        return $this->get_call('v1/users/transactions/crypto/', $params, 1, 0);
    }
	public function UserTransfersFiat($type = array('deposit', 'withdrawal'), $symbol = array("btc","eth","xrp","ltc","usdt","xlm","neo","try"), $startDate = NULL, $endDate = NULL)
    {
        $params = array('type' => $type, 'symbol' => $symbol);
        if ($startDate) { $params['startDate'] = $startDate; }
        if ($endDate) { $params['endDate'] = $endDate; }
        return $this->get_call('v1/users/transactions/fiat/', $params, 1, 0);
    }
}
