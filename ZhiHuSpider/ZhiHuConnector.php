<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 15:21
 */

namespace ZhiHuSpider\ZhiHuSpider;

class ZhiHuConnector
{
	protected $curl;
	protected $response;
	protected $cookiePath;
	protected $Xsrf;
	protected $Hash;

	public function __construct($url)
	{
		echo "初始化连接器...\n";
		$this -> curl = curl_init($url);
	}

	public function setHash()
	{
		$this -> setOpt(CURLOPT_URL, 'https://www.zhihu.com');
		$this -> setOpt(CURLOPT_HTTPGET, 1);
		$page = $this -> exec();
		preg_match_all('/user_hash":"(.*?)"/', $page, $hash);
		return $this -> Hash = trim($hash[1][0]);
	}

	public function getHash()
	{
		return $this -> Hash;
	}

	public function setXsrf($COOKIE)
	{
		$location = strpos($COOKIE, '_xsrf');
		return $this -> Xsrf = trim(substr($COOKIE, $location + 6, 33));
	}

	public function getXrsf()
	{
		return $this -> Xsrf;
	}
	public function setURL($url)
	{
		curl_setopt($this -> curl, CURLOPT_URL, $url);
	}

	public function setOpt($opt, $value)
	{
		curl_setopt($this -> curl, $opt, $value);
	}

	public function exec()
	{
		return $this -> response = curl_exec($this -> curl);
	}

	public function getResponseCode()
	{
		return curl_getinfo($this -> curl, CURLINFO_HTTP_CODE );

	}
	public function getCURL()
	{
		return $this -> curl;
	}

	public function setCookiePath($pathName)
	{
		$this -> cookiePath = $pathName;
	}

	public function cookiePath()
	{
		return $this -> cookiePath;
	}
}