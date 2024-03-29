<?php
    class TopClient{
        
        public $appkey;
        public $secretKey;
        public $proxy;
        // domain client
        public $domain;
        public $client;
        // 生产
        public $gatewayUrl = "http://router.itaoke.org/api";
        // 备用
        //public $gatewayUrl = "http://router.itaokecms.com/api";
        
        public $format = "json";
        /** 是否打开入参check**/
        public $checkRequest = true;
        protected $signMethod = "md5";
        protected $apiVersion = "1.0";
        protected $sdkVersion = "top-sdk-php-20190618";
        public $_CachePath = ITK_DATA_PATH   ;
        public $_method = "";
        public $_saveCache = false;
        
        public function __construct(){
            $this->_CachePath = $this->_CachePath . "runtime/ItaokeAPI/";
        }
        
        protected function generateSign($params){
            ksort($params);
            $stringToBeSigned = $this->secretKey;
            foreach ($params as $k => $v)
            {
                if("@" != substr($v, 0, 1))
                {
                    $stringToBeSigned .= "$k$v";
                }
            }
            unset($k, $v);
            $stringToBeSigned .= $this->secretKey;

            return strtoupper(md5($stringToBeSigned));
        }
        
        public function saveCacheData ($id,$result){
            if($this->_saveCache == false) return false;
            $idkey = substr($id,0,2);
            if (!is_dir($this->_CachePath)) {
                mkdir($this->_CachePath);
            }
            if (!is_dir($this->_CachePath .$this->_method)) {
                mkdir($this->_CachePath .$this->_method);
            }
            if (!is_dir($this->_CachePath .$this->_method.'/'.$idkey)) {
                mkdir($this->_CachePath .$this->_method.'/'.$idkey);
            }
            $filepath = $this->_CachePath .$this->_method.'/'.$idkey;
            if (is_dir($filepath)) {
                $filename = $filepath .'/'.$id .'.cache';
                @file_put_contents($filename,$result);
            }
        }
        
        public function getCacheData ($id){
            $idkey = substr($id,0,2);
            $filename = $this->_CachePath .$this->_method .'/'.$idkey .'/'.$id .'.cache';
            if(file_exists($filename)){
                return @file_get_contents($filename);
            }
            return false;
        }
        
        public function curl($url, $postFields = null){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
//            curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!empty($this->proxy)) {
                $headers = [
                   'X-PROXY:' .  $this->proxy,
                ];
                curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
            }
            //https 请求
            if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            if (is_array($postFields) && 0 < count($postFields)){
                $postBodyString = "";
                $postMultipart = false;
                foreach ($postFields as $k => $v){
                    if("@" != substr($v, 0, 1))//判断是不是文件上传
                    {
                        $postBodyString .= "$k=" . urlencode($v) . "&";
                    }
                    else//文件上传用multipart/form-data，否则用www-form-urlencoded
                    {
                        $postMultipart = true;
                    }
                    $postMultipart = true;
                }
                unset($k, $v);
                curl_setopt($ch, CURLOPT_POST, true);
                if ($postMultipart){
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                }else{
                    
                    curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
                }
            }
            $reponse = curl_exec($ch);
            if (curl_errno($ch)){
                throw new Exception(curl_error($ch),0);
            }else{
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode){
                    throw new Exception($reponse,$httpStatusCode);
                }
            }
            curl_close($ch);
            return $reponse;
        }
        
        protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt){
            $localIp = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "CLI";
            $logger = new LtLogger;
            $logger->conf["log_file"] = $this->_CachePath . "itaoke/top_comm_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";
            $logger->conf["separator"] = "^_^";
            $logData = array(
                             date("Y-m-d H:i:s"),
                             $apiName,
                             $this->appkey,
                             $localIp,
                             PHP_OS,
                             $this->sdkVersion,
                             $errorCode,
                             str_replace("\n","",$responseTxt)
                             );
            $logger->log($logData);
        }
        
        public function execute($request, $session = null){
            $this->_method = $request->getApiMethodName();
            if(!$this->appkey){
                $result = '{"status":"1002","msg":"Missing Itaoke App Key"}';
                $logger = new LtLogger;
                $logger->_saveLog = true;
                $logger->conf["log_file"] = $this->_CachePath . "itaoke_logs/top_biz_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";
                $logger->log(array(
                                   date("Y-m-d H:i:s"),
                                   $this->_method,
                                   $result
                                   ));
                return json_decode($result,true);
            }
            if($this->checkRequest) {
                try {
                    $request->check();
                } catch (Exception $e) {
                    $result->status = $e->getCode();
                    $result->msg = $e->getMessage();
                    return $result;
                }
            }
            //组装系统参数
            $sysParams["app_key"] = $this->appkey;
            //    $sysParams["app_secret"] = $this->secretKey;
            $sysParams["v"] = $this->apiVersion;
            $sysParams["format"] = $this->format;
            $sysParams["sign_method"] = $this->signMethod;
            $sysParams["method"] = $request->getApiMethodName();
            $sysParams["timestamp"] = time();
            // $sysParams["domain"] = $_SERVER['SERVER_NAME'];
            $sysParams["domain"] = $this->domain ?? "tbxzs.com";
            $sysParams["client"] = $this->client ?? "39.88.38.251"; //"127.0.0.1";
            $sysParams["partner_id"] = $this->sdkVersion;
            
            if (null != $session){
                $sysParams["session"] = $session;
            }
            //获取业务参数
            $apiParams = $request->getApiParas();

            // dd(array_merge($apiParams, $sysParams));
            //签名
            $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));
            
            //系统参数放入GET请求串
            //    $requestUrl = $this->gatewayUrl.$sysParams['method']. "?";
            $requestUrl = $this->gatewayUrl. "?";
            
            foreach ($sysParams as $sysParamKey => $sysParamValue){
                $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
            }
            $requestUrl = substr($requestUrl, 0, -1);
            $cacheid = md5($this->createStrParam($apiParams));
            if (!$resp = $this->getCacheData($cacheid)) {
                //发起HTTP请求
                $logger = new LtLogger;
                $logger->conf["log_file"] = $this->_CachePath . "itaoke_logs/logs_" . $this->appkey . "_" . date("Y-m-d") . ".log";
                $logger->log(array(
                                   $this->appkey,
                                   $this->_method,
                                   date("Y-m-d H:i:s")
                                   ));
                try{
                    // dd($requestUrl,$apiParams,$sysParams);
                    $resp = $this->curl($requestUrl, $apiParams);
                    //p($apiParams);
                    if(strlen($resp)>50){
                        $this->saveCacheData($cacheid,$resp);
                    }
                }catch (Exception $e){
                    throw $e;
                    $this->logCommunicationError($sysParams["method"],$requestUrl,"HTTP_ERROR_" . $e->getCode(),$e->getMessage());
                    $result->status = $e->getCode();
                    $result->msg = $e->getMessage();
                    return $result;
                }
            }
            //解析TOP返回结果
            $respWellFormed = false;
            if ("json" == $this->format){
                $respObject = json_decode($resp,true);
                if (null !== $respObject){
                    $respWellFormed = true;
                    /*    foreach ($respObject as $propKey => $propValue){
                     $respObject = $propValue;
                     } */
                }
            }else if("xml" == $this->format){
                $respObject = @simplexml_load_string($resp);
                if (false !== $respObject){
                    $respWellFormed = true;
                }
            }
            
            //返回的HTTP文本不是标准JSON或者XML，记下错误日志
            if (false === $respWellFormed){
                $this->logCommunicationError($sysParams["method"],$requestUrl,"HTTP_RESPONSE_NOT_WELL_FORMED",$resp);
                $result->status = 10000;
                $result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";
                return $result;
            }
            //如果TOP返回了错误码，记录到业务错误日志中
            if ($respObject['status']!='0000'){
                $logger = new LtLogger;
                $logger->conf["log_file"] = $this->_CachePath . "itaoke_logs/top_biz_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";
                $logger->log(array(
                                   date("Y-m-d H:i:s"),
                                   $resp
                                   ));
            }
            return $respObject;
        }
        
        public function createStrParam ($paramArr){
            $strParam = array();
            foreach ($paramArr as $key =>$val) {
                if ($key != ''&&$val != '') {
                    $strParam []= $key .'='.urlencode($val);
                }
            }
            return implode('&',$strParam);
        }
        
        public function exec($paramsArray){
            if (!isset($paramsArray["method"])){
                trigger_error("No api name passed");
            }
            $inflector = new Inflector;
            $inflector->conf["separator"] = ".";
            $requestClassName = ucfirst($inflector->camelize(substr($paramsArray["method"], 7))) . "Request";
            if (!class_exists($requestClassName)){
                trigger_error("No such api: " . $paramsArray["method"]);
            }
            $session = isset($paramsArray["session"]) ? $paramsArray["session"] : null;
            $req = new $requestClassName;
            foreach($paramsArray as $paraKey => $paraValue){
                $inflector->conf["separator"] = "_";
                $setterMethodName = $inflector->camelize($paraKey);
                $inflector->conf["separator"] = ".";
                $setterMethodName = "set" . $inflector->camelize($setterMethodName);
                if (method_exists($req, $setterMethodName)){
                    $req->$setterMethodName($paraValue);
                }
            }
            return $this->execute($req, $session);
        }
        
        public function load_api($api_name){
            include_once('request/'.$api_name.'.php');
            return new $api_name;
        }
    }
