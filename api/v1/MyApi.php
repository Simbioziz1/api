<?php
require_once 'API.class.php';
class MyApi extends API
{
	private $dbName = 'test';
    private $dbHost = 'localhost';
    private $dbUser = 'root';
    private $dbPassword = 'root';
	private $mysqli;
	
	public function __construct($request)
	{
		$this->mysqli = new MySQLi($this->dbHost,$this->dbUser,$this->dbPassword,$this->dbName);
		if (isset($request['key']))
		{
			if (!$this->security($request['key']))
				throw new Exception('Invalid key');
		}
		else
			throw new Exception('Invalid key');
		parent::__construct($request);
		if ($this->mysqli->connect_errno) {
			throw new Exception('MySQLi DB error');
		}
	}
	
	private function security($key)
	{
		$result = $this->mysqli->query('SELECT id FROM token WHERE token = "'.md5($key).'"');
		if($result->num_rows>0)
			return true;
		else
			return false;		
	}
	
	public function getuser($data)
	{
		if($this->method == 'GET')
		{
			$param = array();
			if(isset($data['nick'])) $param['nick'] = $data['nick'];
			if(isset($data['login'])) $param['login'] = $data['login'];
			if(isset($data['email'])) $param['email'] = $data['email'];
			if(isset($data['id'])) $param['id'] = $data['id'];
			if(count($param)>0)
			{
				$i=0;
				if(isset($data['fields']))
					$query = 'SELECT '.$data['fields'].' FROM users WHERE ';
				else
					$query ='SELECT * FROM users WHERE ';
				foreach($param as $key => $value)
				{
					if($i!=0)
						$query.= 'AND '.$key.'= "'.$value.'"';
					else
						$query.= $key.'= "'.$value.'"';
					$i++;
				}
				if($temp= $this->mysqli->query($query)){
					$result = array();
					$i=0;
					while($row=$temp->fetch_assoc())
					{
						$i++;
						$result['user'.$i] = $row;
					}
					$temp->free();				
					return $this->_response($result);
				}
				else
					return $this->_response("No result",404);
			}
			else
				return $this->_response("No Endpoint parameters", 404);
		}
		else
			return $this->_response("Method Not Allowed", 405);
	}
	
	public function setuser($data)
	{
		if($this->method == 'POST')
		{
			if(isset($data['id']) && (isset($data['nick']) || isset($data['email'])))
			{
				$query = 'UPDATE users SET ';
				if(isset($data['nick'])){
					if(isset($data['email']))
						$query.= 'nick = "'.$data['nick'].'", email = "'.$data['email'].'"';
					else
						$query.= 'nick = "'.$data['nick'].'"';
				} 
				else 
					$query.= 'email="'.$data['email'].'"';
				$query.=' WHERE id="'.$data['id'].'"';
				if($this->mysqli->query($query))
					return $this->_response(array('result' => 'sucess'));
				else
					return $this->_response("Update error",404);	
			}
			else
				return $this->_response("No Endpoint parameters",404);
		}
		else
			return $this->_response("Method Not Allowed", 405);
	}
	
	public function __destruct()
	{
		$this->mysqli->close();
	}
}
?>