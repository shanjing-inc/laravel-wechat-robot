<?php
/**
 * TOP API: itaoke.robot.forward.image request
 *
 * @author auto create
 * @since 1.0, 2026.05.08
 */
class ItaokeRobotForwardImageRequest
{
    private $apiParas = array();
    private $robot_id;
    private $wx_id;
    private $aes_key;
    private $file_id;
    private $mid_img_length;
    private $thumb_img_length;
    private $thumb_width;
    private $thumb_height;
    private $big_img_length;
    private $md5;

    public function getApiMethodName()
    {
        return "itaoke.robot.forward.image";
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function check()
    {

    }

    public function putOtherTextParam($key, $value) {
        $this->apiParas[$key] = $value;
        $this->$key = $value;
    }
}
