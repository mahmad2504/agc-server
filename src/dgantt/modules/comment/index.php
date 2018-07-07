<?php


require_once(COMMON);

$gan = new Gan($GAN_FILE);

//var_dump($gan->Project->AuditMr);
//echo $gan->Jira->url.EOL;
			


//project=NUMR and status=Committed and fixVersion is empty
Jirarest::SetUrl($gan->Jira->url,$gan->Jira->user,$gan->Jira->pass);

$stop_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

$date=date_create($date);

$date= date_format($date,"Y-m-d");



$jtasks = Jirarest::Search("project=siep and updated>=".$date." and updated<=".$stop_date,200,"key");
foreach($jtasks as $jtask)
{
	//var_dump($jtask);
	$comment_array = Jirarest::GetComments($jtask['key']);
	$str = '<h1>'.$jtask['key'].'</h1>';
	foreach($comment_array as $comment)
	{
			if( strtotime($comment->date)>= strtotime($date))
			{
				if(strlen($str) > 0)
				{
					echo $str;
					$str="";
				}
				//$cdate = date('Y-m-d', strtotime($comment->date));
				echo '<strong>['.$comment->author.']</strong>'."  ".$comment->comment.EOL;
			}
			
	}
	
	//var_dump($comment_array);
}
//var_dump($jtasks);	
//Jirarest::GetComments("SIEP-183");
return;

?>
