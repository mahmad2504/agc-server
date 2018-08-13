<?php
/**
 *  OpenAir class to communicate with OpenAir Netsuite server 
 *  This class implements functions to communicate with OpenAir via Rest XML 
 *  A PHP App must create the instance of this class. 	
 */
class OpenAir
{
	private $xml;
    private $namespace;
    private $key;
    private $api_ver;
    private $client;
    private $client_ver;
    private $url;
    private $debug = false;
	private $auth=null;
	private $read_commands = [];
	
	/*!
    Constructor of OpenAir Class
	Contact the OpenAir Support Department or your account representative to request API access. See
	Troubleshooting for instructions. When access is granted, you will receive an API namespace and an API
	key. These are the two pieces of information required for API access in addition to your regular OpenAir
	login credentials.

    @param[in] $key         Open Air api key. Talk with service provider for API access key 
    @param[in] $namespace   Open Air api namepace. Default namespace is 'default'
    @param[in] $api_ver     Open Air api version. Default is = '1.0'
	@param[in] $client      Client name
	@param[in] $client_ver  Client version
	@param[in] $url         Openair url
    */
    function __construct($key,$namespace="default", $api_ver = '1.0', $client = 'agc', $client_ver = '1.1', $url='https://www.openair.com/api.pl')
	{
		$this->namespace = $namespace;
        $this->key = $key;
        $this->api_ver = $api_ver;
        $this->client = $client;
        $this->client_ver = $client_ver;
		$this->url = $url;
	}	
	/*!
    Executes all the added commands
    @param[in] $reset_on_success  If set to 1, all added Comands will be removed after execution. If 0, these commands will be retained
	and will be executed again if this function is called again.
    @returns   0 on success, 1 on failure
	*/
	public function Execute($reset_on_success=1)
	{
        $xml = $this->_buildRequest();
        if($this->debug)
		{
			echo "<pre>REQUEST: ";
			var_dump($xml);
			echo "</pre>";
		}
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpcode === 200)
		{
			$xml = simplexml_load_string($result,"SimpleXMLElement", LIBXML_NOCDATA);
			if ($xml === false) 
			{
				die('Error parsing XML');   
			}
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			
         
			if($this->debug)
				echo "Auth status = ".$array['Auth']['@attributes']['status'].'<br>';
			if(count($this->read_commands) == 1)
			{
				if($this->debug)
					echo "Read status = ".$array['Read']['@attributes']['status'].'<br>';
				
				$command = $this->read_commands[0];
				if($array['Read']['@attributes']['status'] == 0)
				{
					$command->_setResults($array['Read'][$command->type]);
				}
				else
					$command->_setResults($array['Read']['@attributes']['status']);
			}
			else
			{
				$i=0;
				foreach($this->read_commands as $command)
				{
					if($this->debug)
						echo "Read status = ".$array['Read'][$i]['@attributes']['status'].'<br>';
					
					if($array['Read'][$i]['@attributes']['status'] == 0)
						$command->_setResults($array['Read'][$i][$command->type]);
					else
						$command->_setResults($array['Read'][$i]['@attributes']['status']);
					$i++;
				}
			}
			if($reset_on_success == 1)
			{
				$this->read_commands = array();
			}
			//var_dump($array);
			return 0;
            //return new Response($result);
        }
		else
		{
			echo "Http Error Code is ".$httpcode;
            return -1;
        }
    }
	
	private function _buildRequest()
	{
		$dom = new DOMDocument;
        if($this->debug)$dom->formatOutput = true;
        $request = $dom->createElement('request');

        // api version
        $apiVer = $dom->createAttribute('API_ver');
        $apiVer->value = $this->api_ver;
        $request->appendChild($apiVer);

        // client
        $client = $dom->createAttribute('client');
        $client->value = $this->client;
        $request->appendChild($client);

        // client_ver
        $client_ver = $dom->createAttribute('client_ver');
        $client_ver->value = $this->client_ver;
        $request->appendChild($client_ver);

        // namespace
        $namespace = $dom->createAttribute('namespace');
        $namespace->value = $this->namespace;
        $request->appendChild($namespace);

        // key
        $key = $dom->createAttribute('key');
        $key->value = $this->key;
        $request->appendChild($key);
		
		if($this->auth != null)
		{
			$request->appendChild($this->auth->_buildRequest($dom));
		}
		else
		{
			die("Authentication Information not added");

		}
		
		foreach($this->read_commands as $command)
		{
			$request->appendChild($command->_buildRequest($dom));
		}
		$dom->appendChild($request);
        $this->xml = $dom->saveXML();
        return $this->xml;
    }
	
	function AddAuth($auth)
	{
		$this->auth = $auth;
	}
	/*!
    Adds command on stack which will later be executed when Execute Function is called. Object should be of base type ReadCommand
	@param[in] $command Object of type ReadCommand
	*/
	public function AddCommand($command)
	{
		if (!is_a($command, 'ReadCommand')) 
		{
			echo "command is not of type ReadCommand";
			return;
		}
		if($command->cmdtype = 'oa_read_command')
			$this->read_commands[] = $command;
		else
			echo "Cmd type is not 'oa_read_command' , not implemented yet";
	}
	/*!
    Adds command on stack to read Project data 
    @param[in] $name string, project name  e.g 'ABC Project'
	@returns object of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
	*/
	public function ReadProjectByName($name) //Returns object of type ReadCommand
	{
		$cprojects = new Command_ReadProjectByName($name);
		$this->AddCommand($cprojects);
		$response = new Response($cprojects);
		return $response;
	}
	
	private function _ReadProjectById($id) //Returns object of type ReadCommand
	{
		$cprojects = new Command_ReadProjectById($id);
		$this->AddCommand($cprojects);
		$response = new Response($cprojects);
		return $response;
	}
	/*!
    Adds command on stack to read Project data 
    @param[in] $ids integer, project id  e.g 354
	@param[in] $ids Comma delimited string for multiple project ids  e.g '354,254'
    @returns array of object of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
	*/
	public function ReadProjectById($in,$field='id') // comma delimited ids as input. Returns array of objects of type ReadCommand
	{
		$ids = array();
		$handles = array();
		if (is_a($in, 'Response')) 
		{
			foreach($in->$field as $f)
				$ids[$f] = $f;
		}
		else
		{
			$ids = explode(",",$in);
		}
		foreach($ids as $key=>$id)
		{
			$handles[] = $this->_ReadProjectById($id);
		}
		$response = new Response($handles);
		return $response;
	}
	private function _ReadTasksByProjectId($projectid)
	{
		$ctask = new Command_ReadTasksByProjectId($projectid,100);
		$this->AddCommand($ctask);
		return $ctask;
	}
	/*!
    Adds command on stack to read project data 
    @param[in] $projectids can be integer user id e.g 456
	@param[in] $projectids can be comma delimited string containing project id  e.g '456,233'
    @returns  array of objects of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
	*/
	public function ReadTasksByProjectId($in,$field='id')
	{
		$ids = array();
		$handles = array();
		if (is_a($in, 'Response')) 
		{
			foreach($in->$field as $f)
				$ids[$f] = $f;
		}
		else
		{
			$ids = explode(",",$in);
		}
		foreach($ids as $key=>$id)
		{
			$handles[] = $this->_ReadTasksByProjectId($id);
		}
		$response = new Response($handles);
		return $response;
	}
	
	private function _ReadAssignedUsersByProjectTaskId($projecttaskid)
	{
		$cusers = new Command_ReadAssignedUsersByProjectTaskId($projecttaskid,100);
		$this->AddCommand($cusers);
		$response = new Response($cusers);
		return $response;
	}
	
	/*!
    Adds command on stack to read users assigned to a particuler project task
    @param[in] $projecttaskid integer user id e.g 456
    @returns   object of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
	*/
	public function ReadAssignedUsersByProjectTaskId($in,$field='id')
	{
		$ids = array();
		$handles = array();
		if (is_a($in, 'Response')) 
		{
			foreach($in->$field as $f)
				$ids[$f] = $f;
		}
		else
		{
			$ids = explode(",",$in);
		}
		foreach($ids as $key=>$id)
		{
			$handles[] = $this->_ReadAssignedUsersByProjectTaskId($id);
		}
		$response = new Response($handles);
		return $response;
	}
	private function _ReadUserById($id)
	{
		$cuser = new Command_ReadUserById($id);
		$this->AddCommand($cuser);
		return $cuser;
	}
	/*!
    Adds command on stack to read  users data. 
    @param[in] $in integer user id e.g 456
	@param[in] $in comma delimited string. e,g '24,35,46'
	@param[in] $in object of ReadCommand with $result populated from some previous read command
	@param[in] $field This parameter is valid only if $in is object of ReadCommand. 
    @returns array of  objects of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
    */
	public function ReadUserById($in,$field='userid') 
	{
		$ids = array();
		$handles = array();
		if (is_a($in, 'Response')) 
		{
			foreach($in->$field as $f)
				$ids[$f] = $f;
		}
		else
		{
			$ids = explode(",",$in);
		}
		foreach($ids as $key=>$id)
		{
			$handles[] = $this->_ReadUserById($id);
		}
		$response = new Response($handles);
		return $response;
	}
	/*!
    Adds command on stack to read all worklogs logged on a particular project task
    @param[in] $projecttaskid id of the project task
    @returns   object of type ReadCommand. For results, access result member of this object. result is filled by  Execute function 
	*/
	function _ReadWorkLogsByProjectTaskId($approved,$projecttaskid)
	{
		$cworklogs = new Command_ReadWorklogsByProjectTaskId($approved,$projecttaskid,1000);
		$this->AddCommand($cworklogs);
		return $cworklogs;
	}
	public function ReadWorkLogsByProjectTaskId($approved,$in,$field='userid') 
	{
		$ids = array();
		$handles = array();
		if (is_a($in, 'Response')) 
		{
			foreach($in->$field as $f)
				$ids[$f] = $f;
		}
		else
		{
			$ids = explode(",",$in);
		}
		foreach($ids as $key=>$id)
		{
			$handles[] = $this->_ReadWorkLogsByProjectTaskId($approved,$id);
		}
		$response = new Response($handles);
		return $response;
	}
	public function ReadWorkLogsByProjectId($projectid,$approved=false)
	{
		$nonbillabletaskids = array();
		$h1 = $this->ReadTasksByProjectId($projectid);
		$this->Execute();
		$tasks = $h1->Data('id','name','non_billable');
		foreach($tasks as $task)
		{
			if($task['non_billable'] == 1)
				$nonbillabletaskids[] = $task['id']; 
		}
		$h2 = $this->ReadWorkLogsByProjectTaskId($approved,$h1,'id');
		$this->Execute();
		//$h2->toString('projecttaskid','date','userid','decimal_hours');
		$worklogs = $h2->Data('projecttaskid','id','date','userid','decimal_hours');
		foreach($worklogs as &$worklog)
		{
			foreach($nonbillabletaskids as $nbtid)
			{
				if($nbtid == $worklog['projecttaskid'])
					$worklog['nonbillable'] = 1;
				else
					$worklog['nonbillable'] = 0;
			}
		}
		//var_dump($worklogs);
		return $worklogs;
	}
	public function ReadProjectId($projectname)
	{
		$h1 = $this->ReadProjectByName($projectname);
		$this->Execute();
		return $h1->Data('id','name');
	}
	public function ReadProjectName($projectid)
	{

		$h1 = $this->ReadProjectById($projectid);
		$this->Execute();
		return $h1->Data('id','name');
	}
	public function ReadUsersByProjectName($projectname)
	{
		//echo $projectname.EOL;
		$h0 = $this->ReadProjectByName($projectname);
		$this->Execute();
		$h1 = $this->ReadTasksByProjectId($h0,'id');
		$this->Execute();
		$h2 = $this->ReadAssignedUsersByProjectTaskId($h1);
		$this->Execute();
		$h3 = $this->ReadUserById($h2,'userid');
		$this->Execute();
		$data = $h3->Data('id','name','currency');
		return $data;
	}
	public function ReadUsersByProjectId($projectid)
	{
		$h1 = $this->ReadTasksByProjectId($projectid);
		$this->Execute();
		$h2 = $this->ReadAssignedUsersByProjectTaskId($h1);
		$this->Execute();
		$h3 = $this->ReadUserById($h2,'userid');
		$this->Execute();
		$data = $h3->Data('id','name','currency');
		$h3->toString('id','name','currency');
		return $data;
	}
}
?>
