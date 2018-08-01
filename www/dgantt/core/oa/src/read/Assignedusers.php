<?php
class Command_ReadAssignedUsersByProjectTaskId extends ReadCommand
{
	function __construct($projecttaskid,$limit=10) 
	{
		$this->type = 'Projecttaskassign';
		$this->method = 'equal to';
		$this->limit = $limit;
		$this->_projecttaskid = $projecttaskid;
	}
	function _buildRequest($dom)
	{
		$read = parent::_buildDefaults($dom);
		$project = $dom->createElement('Projecttaskassign');
		$id = $dom->createElement('projecttaskid',$this->_projecttaskid);
		
		$project->appendChild($id);
		$read->appendChild($project);
		
		return $read;
    }
	function toString()
	{
		if(parent::toString()==-1)
			return;
		echo "---------------Assigned Users (projectaskid=$this->_projecttaskid, limit=$this->limit) --------------".EOL;	
		$userids = $this->userid;
		for($i=0;$i<count($userids);$i++)
		{
			echo $userids[$i].EOL;
		}
	}
}	
?>