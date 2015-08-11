<?php
/**

 * http ������
 * @author: yubingzhu
 */
class Http
{
    
    protected $_config = array();
    protected $_cookies = '';
    protected $_heads = array();
    protected $_ch = '';
    
    private function __construct() {
        $this->_config = array('timeout'=>2);
    }

	static public function getInstance(){
		static $instance;
		if (!isset($instance)){
			$c = __CLASS__;
			$instance = new $c;
		}
		return $instance;
	}
    
    /**
     * ����cookies
     *
     * @param Array $cookie $cookie['uin']=4093845;
     * @return $this
     */
    public function setCookie($cookie)   {
        if (!is_array($cookie)) {
            $this->_cookies .=$cookie;
        } else {
            foreach($cookie as $k=>$v) {
                $this->_cookies .= "$k=$v;";
            }   
        }
        return $this;
    }
        
    /**
     * ��������ͷ
     *
     * @param Array $headMap, $head['Host'] = 'www.example.com'
     * @return $this
     */
    public function setHeader($headMap)    {
        if (!is_array($headMap)) {
            return $this;
        }
        $this->_heads = array_merge($this->_heads, $headMap);
        return $this;
    }
    
    /**
     * ���û�������
     * ֻ������������
     * timeout:��ʱʱ��
     * proxy: ��������� tcp://10.1.1.2:8080
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config)    {
        $this->_config = array_merge($this->_config, $config);
        return $this;
    }
    
    
    /**
     * ��ȡִ��״����Ϣ
     */
    public function getInfo($opt=null)    {
        return $opt==null ? curl_getinfo($this->ch) : curl_getinfo($this->ch,$opt);
    }
    
    /**
     * ��ȡhttp������
     */
    public function getCode()    {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }
    
    /**
     * ��ȡ������Ϣ
     */
    public function getError()    {
        return curl_error($this->ch);
    }
    
    /**
     * ���� ֧��get��post���ַ�ʽ
     *
     * @param string $url http://www.soso.com
     * @param  string $postdata post���� ���øò�����ʹ��post����,����ʹ��get����
     * @return data on succ, false on fail
     */
    public function request($url, $postdata = '')    {
        if ('' === $url) {
            return false;
        }
        
        $this->ch = curl_init();
        if (false === $this->ch) {
            return false;
        }
        
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
            foreach($this->_config as $k=>$v) {
            $k = strtoupper($k);
            switch ($k) {
            case 'PROXY':
                curl_setopt($this->ch, CURLOPT_PROXY, $v);
                break;
            case 'TIMEOUT':
                curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $v);
                curl_setopt($this->ch, CURLOPT_TIMEOUT, $v);
                break;
            default:
                is_long($k) && curl_setopt($this->ch,$k,$v);
                break;
            }
        }
        if ($this->_heads) {
            $header = array();
            foreach($this->_heads as $k=>$v)
            {
                $header[] = "$k: $v";
            }
            
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        }
        
        if ($this->_cookies) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $this->_cookies);
        }
        
        if ($postdata) {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postdata);
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        }
        
        $response = curl_exec($this->ch);
        
        return $response;
    }

    
    public function __destruct()
    {
        if ($this->ch !=null) {
            curl_close($this->ch);
        }
    }
}
/*
  $config['timeout'] = 1;
  //$header['Host'] = 'www.soso.com';
  $header['User-Agent']='Mozilla/5.0 (Windows NT 5.1; rv:19.0) Gecko/20100101 Firefox/19.0';
  $cookie['uin'] = 40930845;
  $cookie['skey'] = '@xdf803380';
 

  echo $html = http::getInstance()->setConfig($config)->setCookie($cookie)->setHeader($header)
           ->request('http://www.soso.com/q?w=test');


$url ="http://www.baidu.com";
echo http::getInstance()->request($url);
*/