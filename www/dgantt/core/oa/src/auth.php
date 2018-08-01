<?php
class Auth
{
	private $company;
	private $user;
	private $password;
	
	function __construct($company,$user,$password)
	{
		$this->company = $company;
		$this->user = $user;
		$this->password = $password;
	}
	function _buildRequest($dom)
	{
		$eauth = $dom->createElement('Auth');
		$elogin = $dom->createElement('Login');
		$ecompany = $dom->createElement('company', $this->company);
		$euser = $dom->createElement('user', $this->user);
		$epassword = $dom->createElement('password', $this->password);
		$elogin->appendChild($ecompany);
		$elogin->appendChild($euser);
		$elogin->appendChild($epassword);
		$eauth->appendChild($elogin);

		return $eauth;
    }
}
?>