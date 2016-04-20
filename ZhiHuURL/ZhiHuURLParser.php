<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 14:51
 */

namespace ZhiHuSpider\ZhiHuURL;


class ZhiHuURLParser
{
	private $XHRPattern;
	private $pattern;
	private $match = array();

	public function __construct($pattern)
	{
		$this -> pattern = $pattern;
	}

	public function parserURL($pageSource)
	{
		preg_match_all($this -> pattern, $pageSource, $this -> match);
	}

	public function setXHRPattern($XHRPattern)
	{
		$this -> XHRPattern = $XHRPattern;
	}

	public function parserURLXHR($XHRSource)
	{
		preg_match_all($this -> XHRPattern, $XHRSource, $this -> match);
	}

	public function getResult()
	{
		return $this -> match;
	}
}