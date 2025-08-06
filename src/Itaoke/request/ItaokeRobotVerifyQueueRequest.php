<?php
/**
 * TOP API: itaoke.robot.verify.queue request
 *
 * @author auto create
 * @since 1.0, 2018.07.25
 */
class ItaokeRobotVerifyQueueRequest
{
	/**
	 * 需返回的字段列表
	 **/
	private $fields;

	/**
	 * JSON数据
	 **/
	private $data;

	private $apiParas = array();

	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setJson($data)
	{
		$this->data = $data;
		$this->apiParas["data"] = $data;
	}

	public function getJson()
	{
		return $this->data;
	}

	public function getApiMethodName()
	{
		return "itaoke.robot.verify.queue";
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