<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 15:17
 */

namespace ZhiHuSpider\ZhiHuSpider;
require_once 'ZhiHuConnector.php';
define('LOGIN_SUCCESS', 1);
define('LOGIN_FAILED', 0);

class ZhiHuLoger
{
	protected $username;
	protected $password;
	protected $loginPage;
	private $connector;

	/**
	 * ZhiHuLoger constructor.
	 * @param $username
	 * @param $password
	 * @param ZhiHuConnector $connector 连接器
	 */
	public function __construct($username, $password, ZhiHuConnector $connector)
	{
		$this -> connector = $connector;
		$this -> username = $username;
		$this -> password = $password;
	}

	public function Login()
	{
		echo "开始登陆...\n";
		/**
		 * 初始化连接获取cookie设置
		 */
		$this -> connector  -> setOpt(CURLOPT_COOKIEJAR, 'CookieFiles/'.$this -> username.'_CookieFile.txt');
		$this -> connector  -> setOpt(CURLOPT_COOKIEFILE, 'CookieFiles/'.$this -> username.'_CookieFile.txt');
		$this -> connector  -> setOpt(CURLOPT_RETURNTRANSFER, 1);
		$this -> connector  -> setOpt(CURLOPT_SSL_VERIFYPEER, 0);
		if (!file_exists('CookieFiles/'.$this -> username.'_CookieFile.txt'))
		{
			$this -> connector  -> exec();
		}

		/**
		 * 通过定位_xsrf字符串来取得32为xsrf值
		 */
		$cookie = file_get_contents('CookieFiles/'.$this -> username.'_CookieFile.txt');

		$xsrf = $this -> connector -> setXsrf($cookie);
		echo "设置xsrf值...\n";
		$pathName = readlink('CookieFiles/'.$this -> username.'_CookieFile.txt');
		$this -> connector -> setCookiePath($pathName);

		/**
		 * xsrf账号密码发起post登陆
		 */
		$this -> connector  -> setOpt(CURLOPT_URL, 'https://www.zhihu.com/login/email');
		$this -> connector  -> setOpt(CURLOPT_POST, 1);
		$POSTFIELDS = '_xsrf='.$xsrf.'&password='.urlencode($this -> password).'&remember_me=true&email='.urlencode($this -> username);
		$this -> connector  -> setOpt(CURLOPT_POSTFIELDS, $POSTFIELDS);

		/**
		 * 登陆成功的unicode码/767b/
		 */
		if (preg_match('/767b/', $this -> connector  -> exec()))
		{
			echo "登陆成功...\n";
			$hash = $this -> connector -> setHash();
			echo "设置Hash...\n";
			return LOGIN_SUCCESS;
		}
		else
		{
			return LOGIN_FAILED;
		}
	}

	public function getConnector()
	{
		return $this -> connector;
	}

	public function setUserName($username)
	{
		$this -> username = $username;
	}

	public function setPassword($password)
	{
		$this -> password = $password;
	}

	public function changeUser($username, $password)
	{
		$this -> $username = $username;
		$this -> $password = $password;
	}
}