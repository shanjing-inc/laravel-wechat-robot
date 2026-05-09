<?php
/**
 * TOP API: itaoke.robot.upload.image request
 *
 * @author auto create
 * @since 1.0, 2026.05.08
 */
class ItaokeRobotUploadImageRequest
{
    private $apiParas = array();
    private $robot_id;
    private $wx_id;
    private $url;

    public function getApiMethodName()
    {
        return "itaoke.robot.upload.image";
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
