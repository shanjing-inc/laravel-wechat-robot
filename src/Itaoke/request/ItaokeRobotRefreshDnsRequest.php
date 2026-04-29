<?php
/**
 * TOP API: itaoke.robot.refresh.dns request
 * 
 * @author auto create
 * @since 1.0, 2025.04.29
 */
class ItaokeRobotRefreshDnsRequest
{
    private $apiParas = array();

    private $robotId;

    public function setRobotId($robotId)
    {
        $this->robotId = $robotId;
        $this->apiParas["robot_id"] = $robotId;
    }

    public function getRobotId()
    {
        return $this->robotId;
    }

    public function getApiMethodName()
    {
        return "itaoke.robot.refresh.dns";
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