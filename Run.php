<?php
/**
 * Created by Yang xunwu.
 * User: mao
 * Date: 2016/3/19
 * Time: 15:43
 */
namespace ZhiHuSpider;
require_once 'RequireFile.php';


$connector = new ZhiHuSpider\ZhiHuConnector('https://www.zhihu.com');
$logger = new ZhiHuSpider\ZhiHuLoger('yxw19920801@gmail.com', 'yang741wu', $connector);
$logger -> Login();
$Container = new ZhiHuURL\ZhiHuURLContainer();
$Parser = new ZhiHuURL\ZhiHuURLParser(ZhiHuHomeSpiderPattern);
$Spider = new ZhiHuUserSpider($logger -> getConnector());
$Spider -> setURLParser($Parser);
$Spider -> setContainer($Container);

$follee = $Spider -> getFolloeesDetail('SheepOnSea');
echo "获取列表结束\n";
$total = count($follee);

for($idx = 1; $idx < $total + 1; $idx++)
{
	$name = $follee[$idx][2];
	$maxPage = $Spider -> findMaxPage($name);
	$Spider -> startFetchUser($name, $maxPage, 500);
	if($Spider -> getContainer() -> getTotalNum() > 0)
	{
		URLContainerTOHTML($Spider -> getContainer() -> getURLContainer(), $follee[$idx][1], 'ZhiHuHome');
	}
	$Spider -> getContainer() ->clean();
}
