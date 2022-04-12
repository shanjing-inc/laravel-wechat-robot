<?php
namespace App\Libraries\Itk;

use Illuminate\Support\Facades\Log;

/**
* 淘客相关信息接口
* @date: 2017-9-26 上午11:26:42
* @author: Caption
* @copyright 2016-2017 ITAOKE.ORG
*/
class Client {
	private $_itkconfig = [
		'app_key' => '1081598962',
		'app_secret' => '5fb0389e-4c43-8b81-c68e-11c7e884e8c6',
	];

	/**
	 * 获取ITK 云端数据
	 */
	public function getItaokeQuan($cinfo) {
		$this->tb_top = $this->_get_itk_top();
		$req = $this->tb_top->load_api('ItaokeCouponsGetRequest');
		// $req->setPageNo($this->sysParams['p']);
		// $req->setCat($this->sysParams['catId']);
		// $req->setSort($this->sysParams['sort']?$this->sysParams['sort']:$cinfo['sort']);
		$req->setMaxPrice($cinfo['max_price']);
		$req->setMinPrice($cinfo['min_price']);
		$req->setMaxVolume($cinfo['max_volume']);
		$req->setMinVolume($cinfo['mix_volume']);
		$req->putOtherTextParam('source_id','');
		$req->putOtherTextParam('ems','');
		$req->putOtherTextParam('activity_type','');
		$req->putOtherTextParam('page_size',20);
		
		$resp = (array)$this->tb_top->execute($req);
		return $resp;
	}

	/**
	 * 获取机器人列表。
	 */
	public function robotList() {
		$this->tb_top = $this->_get_itk_top();
		$req = $this->tb_top->load_api('ItaokeRobotListGetRequest');
		$req->putOtherTextParam('p', 1);
		$req->putOtherTextParam('page_size', 5);
		
		$resp = (array)$this->tb_top->execute($req);
		return $resp;
	}

	/**
	 * 修改机器人
	 */
	public function modifyRobot($params) {
		$api = "ItaokeRobotChangeGetRequest";
		return $this->sendRequest($api, $params);
	}

	/**
	 * 获取机器人列表。
	 */
	public function createRobot($params) {
		$api = "ItaokeRobotCreateGetRequest";
		return $this->sendRequest($api, $params);
	}
	/**
	 * 获取机器人列表。
	 */
	public function sendMsg($params) {
		$api = "ItaokeRobotMacSendTextRequest";
		return $this->sendRequest($api, $params);
	}

	public function groups($params) {
		$api = "ItaokeRobotRoomListDetailRequest";
		return $this->sendRequest($api, $params);
	}
	public function memberDetail($params) {
		$api = "ItaokeRobotFriendDetailRequest";
		return $this->sendRequest($api, $params);
	}

	/**
	 * 
	 * @author Yingjie Feng <fengit@shanjing-inc.com>
	 */
	public function sendRequest($api, $params = [])
	{
		$this->tb_top = $this->_get_itk_top();
		$req = $this->tb_top->load_api($api);

		if ($params) {
			foreach ($params as $key => $value) {
				$req->putOtherTextParam($key, $value);
			}
		}

		$resp = (array)$this->tb_top->execute($req);
		Log::info(['api' => $api, 'req' => $params,'resp' => $resp]);
		return $resp;
	}

	/**
	 * 获取登录二维码
	 */
	public function getLoginQrcode($params) {
        $api = 'ItaokeRobotQrcodeMacloginRequest';
        return $this->sendRequest($api, $params);
	}

	/**
	 * 循环是否登陆（真正的登录接口）
	 */
	public function confirmLogin($params) {
        $api = 'ItaokeRobotAsyncMloginRequest';
        return $this->sendRequest($api, $params);
	}
		
	private function _get_itk_top() {
		define("ITK_DATA_PATH", storage_path("framework/itk/"));

		include_once('Itaoke/TopClient.php');
		include_once('Itaoke/RequestCheckUtil.php');
		include_once('Itaoke/Logger.php');
		$top = new \TopClient;
		$top->appkey = $this->_itkconfig['app_key'];			// 您的ITK  appkey
		$top->secretKey = $this->_itkconfig['app_secret'];		// 您的ITK  appsecret
		return $top;
	}
	
}