<?php

namespace Shanjing\LaravelWechatRobot;


use Illuminate\Support\Facades\Log;

/**
* 淘客相关信息接口
* @date: 2017-9-26 上午11:26:42
* @author: Caption
* @copyright 2016-2017 ITAOKE.ORG
*/
class ItaokeRobotClient
{
    public $appkey; // 您的ITK  appkey
    public $secretKey;

    public function __construct($appkey = null, $secretKey = null) {

        if (!empty($appkey) && !empty($secretKey)) {
            $this->appkey    = $appkey;
            $this->secretKey = $secretKey;

        } else {
            $this->appkey    = Config('wechat.robot.itaoke.app_key');
            $this->secretKey = Config('wechat.robot.itaoke.secret');
        }
    }
    /**********************************************************************************************
     ***************************************     机器人管理     ***********************************
     *********************************************************************************************/

    /**
     * 创建机器人。
     * @param $month     '月数'
     * @param $robotType '机器人类型 1 发单机器人 2转发机器人 3 返利机器人 4全能机器人 5小型机器人 6发圈机器人'
     * @param $wxId      '微信号'
     * @param $agentUid  '代理id'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function createRobot($month, $robotType, $wxId, $agentUid = null)
    {
        $api = "ItaokeRobotCreateGetRequest";
        return $this->sendRequest($api, [
            'month'       => $month,
            'robot_type'  => $robotType,
            'wechatrobot' => $wxId,
            'agent_uid'   => $agentUid,
        ]);
    }

    /**
     * 获取机器人列表。
     * @param int $page  '页'
     * @param int $size  '每页个数'
     * @return array
     * @throws \Exception
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function robotList($page = 1, $size = 20)
    {
        $this->tb_top = $this->_get_itk_top();
        $req = $this->tb_top->load_api('ItaokeRobotListGetRequest');
        $req->putOtherTextParam('p', $page);
        $req->putOtherTextParam('page_size', $size);

        $resp = (array)$this->tb_top->execute($req);
        return $resp;
    }

    /**
     * 获取机器人详情。
     * @param $robotId   '机器人id'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function robotDetail($robotId)
    {
        $api = "ItaokeRobotDetailGetRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
        ]);
    }

    /**
     * 修改机器人
     *
     * @param $robotId   '机器人id'
     * @param $month     '续费 月数'
     * @param $wxId      '微信号 更换号的时候传'
     * @param $groupNum  '群个数'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function modifyRobot($robotId, $month = null, $wxId = null, $groupNum = null)
    {
        $api = "ItaokeRobotChangeGetRequest";
        return $this->sendRequest($api, [
            'robot_id'    => $robotId,
            'month'       => $month,
            'wechatrobot' => $wxId,
            'group_num'   => $groupNum,
        ]);
    }

    /**
     * 强制下线机器人
     *
     * @param $robotId   '机器人id'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function forceOfflineRobot($robotId)
    {
        $api = "ItaokeRobotForceOfflineGetRequest";
        return $this->sendRequest($api, [
            'robot_id'    => $robotId,
        ]);
    }

    /**
     * 重置机器人
     *
     * @param $robotId   '机器人id'
     * @param $changeIp  '是否更换服务器'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function resetRobot($robotId, $changeIp = true)
    {
        $api = "ItaokeRobotResetGetRequest";
        return $this->sendRequest($api, [
            'robot_id'    => $robotId,
            'change_ip'   => $changeIp
        ]);
    }

    /**
     * 删除机器人
     *
     * @param $robotId '机器人id'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function delelteRobot($robotId)
    {
        $api = "ItaokeRobotDeleteGetRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId
        ]);
    }


    /**********************************************************************************************
     *****************************************     登录     ***************************************
     *********************************************************************************************/

    /**
     * 获取登录二维码
     *
     * @param $robotId '机器人id，必传！！' 执行登录接口返回此字段，记得保存数据库里
     * @param [type] $proCode 省编码，可不传，通用编码，比如江苏：320000 
     * @param [type] $cityCode 市编码，可不传，通用编码，比如南京：320100 
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    /**
     * Undocumented function
     *
     * @param [type] $robotId
     * @param [type] $proCode
     * @param [type] $cityCode
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2023-10-11
     */
    public function getLoginQrcode($robotId, $proCode = null, $cityCode = null)
    {
        $api = 'ItaokeRobotQrcodeMacloginRequest';
        $params = [
            'robot_id' => $robotId
        ];
        if (!empty($proCode)) {
            $params['proCode'] = $proCode;
        }
        if (!empty($cityCode)) {
            $params['cityCode'] = $cityCode;
        }
        return $this->sendRequest($api, $params);
    }

    /**
     * 循环是否登陆（真正的登录接口）
     *
     * @param $robotId  '新增机器人接口的内部id'
     * @param $wId      'wId 获取二维码接口返回的微信实例id'
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function confirmLogin($robotId, $wId)
    {
        $api = 'ItaokeRobotAsyncMloginRequest';
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'wId'      => $wId,
        ]);
    }


    /**********************************************************************************************
     ***************************************     群管理     ***************************************
     *********************************************************************************************/
    /** 获取群成员
     * @param $robotId
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function groups($robotId)
    {
        $api = "ItaokeRobotRoomListDetailRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
        ]);
    }

    /**
     * 获取群详情
     * @param $robotId
     * @param $roomId
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function groupDetail($robotId, $roomId)
    {
        $api = "ItaokeRobotRoomDetailRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'room_id'  => $roomId,
        ]);
    }

    /**
     * 获取群成员信息
     * @param $robotId
     * @param $roomId
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function groupMember($robotId, $roomId)
    {
        $api = "ItaokeRobotMacGetChatroomMemberRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'room_id'  => $roomId,
        ]);
    }

    /**
     * 获取好友详情
     * @param $robotId
     * @param $wId
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function memberDetail($robotId, $wId)
    {
        $api = "ItaokeRobotFriendDetailRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'wx_id' => $wId
        ]);
    }



    /**********************************************************************************************
     ***************************************     消息管理     **************************************
     *********************************************************************************************/
    /**
     * 发送文本消息
     * @param $robotId
     * @param $toWxId
     * @param $content
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function sendTextMsg($robotId, $toWxId, $content)
    {
        $api = "ItaokeRobotMacSendTextRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'content' => $content,
            'toWxId' => $toWxId,
        ]);
    }
    
    /**
     * 群聊@发消息
     * @param $robotId - 机器人 id
     * @param $toWxId  - 群 id
     * @param $atWxId  - @ wxID
     * @param $content - 内容
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function sendAtTextMsg($robotId, $toWxId, $atWxId,$content)
    {
        $api = "ItaokeRobotMacGroupAtRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'content' => $content,
            'wx_id' => $toWxId,
            'ant' => $atWxId,
        ]);
    }


    /**
     * 发送图片消息
     * @param $robotId
     * @param $toWxId
     * @param $picUrl
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function sendImageMsg($robotId, $toWxId, $picUrl)
    {
        $api = "ItaokeRobotMacSendImageRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'pic_url' => $picUrl,
            'toWxId' => $toWxId,
        ]);
    }

    /**
     * 转发图片
     * @param $robotId - 机器人
     * @param $toWxId  - 发送微信好友/群id。一般wxid_开头
     * @param $content - 消息 xml 内容
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function forwardImageMsg($robotId, $toWxId, $content)
    {
        $api = "ItaokeRobotMacSendRecvImageRequest";
        return $this->sendRequest($api, [
            'robot_id' => $robotId,
            'wx_id' => $toWxId,
            'content' => $content,
        ]);
    }

    /**
     * 发送卡片消息
     * @param $robotId
     * @param $toWxId
     * @param $picUrl
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function sendCardMsg($robotId, $toWxId, $title, $url, $description, $thumbUrl)
    {
        $api = "ItaokeRobotMacSendCardRequest";
        return $this->sendRequest($api, [
            'robot_id'    => $robotId,
            'wx_id'       => $toWxId,
            'title'       => $title,
            'url'         => $url,
            'description' => $description,
            'thumbUrl'    => $thumbUrl,

        ]);
    }

    /**
     * 发送小程序
     * @param $robotId
     * @param $toWxId
     * @param $picUrl
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function sendAppMsg($robotId, $toWxId, $displayName, $iconUrl, $id, $pagePath, $title, $username, $thumbUrl)
    {
        $api = "ItaokeRobotMacSendAppRequest";
        return $this->sendRequest($api, [
            'robot_id'     => $robotId,
            'to_wx_id'     => $toWxId,
            'display_name' => $displayName,
            'icon_url'     => $iconUrl,
            'id'           => $id,
            'page_path'    => $pagePath,
            'thumb_url'    => $thumbUrl,
            'title'        => $title,
            'user_name'    => $username,

        ]);
    }

    /**********************************************************************************************
     ***************************************     基础方法     **************************************
     *********************************************************************************************/
    /**
     * 获取ITK 云端数据
     */
    public function getItaokeQuan($cinfo)
    {
        $this->tb_top = $this->_get_itk_top();
        $req = $this->tb_top->load_api('ItaokeCouponsGetRequest');
        // $req->setPageNo($this->sysParams['p']);
        // $req->setCat($this->sysParams['catId']);
        // $req->setSort($this->sysParams['sort']?$this->sysParams['sort']:$cinfo['sort']);
        $req->setMaxPrice($cinfo['max_price']);
        $req->setMinPrice($cinfo['min_price']);
        $req->setMaxVolume($cinfo['max_volume']);
        $req->setMinVolume($cinfo['mix_volume']);
        $req->putOtherTextParam('source_id', '');
        $req->putOtherTextParam('ems', '');
        $req->putOtherTextParam('activity_type', '');
        $req->putOtherTextParam('page_size', 20);

        $resp = (array)$this->tb_top->execute($req);
        return $resp;
    }

    /**
     *
     * @author Yingjie Feng <fengit@shanjing-inc.com>
     */
    public function sendRequest($api, $params = [])
    {
        $this->tb_top = $this->_get_itk_top();
        // 设置代理
        if (key_exists('robot_id', $params)) {
            $proxy = $this->getProxy($params['robot_id']);
            if (!empty($proxy)) {
                $this->tb_top->proxy = $proxy;
            }
        }
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

    private function _get_itk_top()
    {
        if (!defined('ITK_DATA_PATH')) {
            define("ITK_DATA_PATH", storage_path("framework/itk/"));
        }

        include_once('Itaoke/TopClient.php');
        include_once('Itaoke/RequestCheckUtil.php');
        include_once('Itaoke/Logger.php');

        $top = new \TopClient();
        $top->appkey    = $this->appkey;   // 您的ITK  appkey
        $top->secretKey = $this->secretKey; // 您的ITK  appsecret
        return $top;
    }

    /**
     * 获取代理
     *
     * @param [type] $robotId
     * @return void
     * @example
     * @author lou@shanjing-inc.com
     * @since 2023-01-05
     */
    private function getProxy($robotId) {
        if(function_exists('getItaokeRobotProxy')){
            return getItaokeRobotProxy($robotId);
        }
        return null;
    }
}
