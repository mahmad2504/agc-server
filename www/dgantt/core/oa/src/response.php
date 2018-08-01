<?php
class Response
{
	private $cmd = array();
	function __construct($cmd) 
	{
		if(is_array($cmd))
		{
			foreach($cmd as $c)
				$this->cmd[] = $c;
		}
		else
			$this->cmd[] = $cmd;
	}
	public function __get($name)
	{
		$data = array();
		//$data_dup_check = array();
		foreach($this->cmd as $cmd)
		{
			//var_dump($cmd->result);
			$val = $cmd->$name;
			foreach($val as $v)
			{
				//if(!array_key_exists($v,$data_dup_check))
				{
					//$data_dup_check[$v] = 1;
					$data[] = $v;
				}
			}
		}
		return $data;
	}
	public function DateToString($val)
	{
		return $val['Date']['year']."-".$val['Date']['month']."-".$val['Date']['day'];
	}
	private function PrepareOutput($args,$debug=0)
	{
		$data = array();
		$returndata = array();
		if(count($args) == 0)
		{
			foreach($this->cmd as $cmd)
			{
				$cmd->toString();
			}
		}
		else
		{
			foreach($args as $arg)
			{
				//$array_$arg = array();//$this->$arg;
				$data[] = $this->$arg;
				//var_dump($this->$arg);
			}
			for($i=0;$i<count($data[0]);$i++)
			{
				for($j=0;$j<count($data);$j++)
				{
					if(strtolower($args[$j]) == 'date')
						$returndata[$i][$args[$j]]=$this->DateToString($data[$j][$i]);
					else
						$returndata[$i][$args[$j]]=$data[$j][$i];
					if($debug)
					{
						echo "(".$args[$j].")".$returndata[$i][$args[$j]]." ";
					}
				}
				if($debug)
					echo EOL;
			}
		}
		return $returndata;
	}
	function toString()
	{
		$args = func_get_args();
		return $this->PrepareOutput($args,1);
	}
	function Data()
	{
		$args = func_get_args();
		return $this->PrepareOutput($args,0);
	
	}
	
}

?>
