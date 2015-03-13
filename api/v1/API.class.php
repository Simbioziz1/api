<?php
abstract class API{
	protected $args = Array();
	protected $endpoint = '';
	protected $method = '';
	protected $retmethod = 'json';
	protected $request = array();
	protected $xmlout;
	
	public function __construct($request)
	{
		$this->args = explode('/',rtrim($request['request'], '/'));
		$this->retmethod = array_shift($this->args);
		$this->endpoint = array_shift($this->args);
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->request = $request;
	}
	
	public function processAPI() {
		if(method_exists($this,$this->endpoint)){
			return $this->{$this->endpoint}($this->request);
		}
		else
		{
			return $this->_response("No Endpoint: $this->endpoint", 404);
		}
	}
	
	private function _xmlresponse($data, $fkey = false, $parent = false)
	{
		if($fkey != false)
		{
			if($parent != false)
				$temp = $parent->addChild($fkey);
			else
				$temp = $this->xmlout->addChild($fkey);
			foreach($data as $key => $value)
			{
				if(!is_array($value))
						$temp->addChild($key,$value);
				else
					$this->_xmlresponse($value,$key,$temp);
			}
			return true;
		}
		else
		{
			foreach($data as $key => $value)
			{
				if(!is_array($value))
						$this->xmlout->addChild($key,$value);
				else
					$this->_xmlresponse($value,$key);
			}
			return true;
		}
	}
	
	public function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		if($status == 200){
			if($this->retmethod == 'json'){
				header("Content-Type: application/json");
				return json_encode($data);
			} else {
				
				$this->xmlout = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><data></data>");
				$this->_xmlresponse($data);
				header("Content-Type: application/xml");
				return $this->xmlout->asXML();
			}
		}
		else
		{
			if($this->retmethod == 'json'){
				header("Content-Type: application/json");
				return json_encode(array('error' => $data.' '.$status));
			} else {
				$out = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><errors></errors>");
				$out->addChild('error', $data.' '.$status);
				header("Content-Type: application/xml");
				return $out->asXML();
			}
		}
    }
	
	private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}
?>