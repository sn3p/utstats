<?php

class ftp {
	var $lastmsg;
	var $host;
	var $fullhost;
	var $port;
	var $fp;
	var $transfertype;
 	var $errors = array();
		
	function SetServer($host, $port) {
		if (!is_long($port)) {
			$this->lastmsg =  "Incorrect port syntax";
			return(false);
		} else {
			$ip=@gethostbyname($host);
			$dns=@gethostbyaddr($host);
			if(!$ip) $ip = $host;
			if(!$dns) $dns = $host;
			if(ip2long($ip) === -1) {
				$this->lastmsg("Wrong host name/address \"".$host."\"");
				return(false);
			}
		$this->host = $ip;
		$this->fullhost = $dns;
		$this->port = $port;
		}
		return(true);
	}
	
	function connect() {
		$php_erormsg = '';
		if (!check_extension('ftp') or true) {
			$this->lastmsg = "No FTP support in this php build!";
			return(false);
		}
		$this->fp = @ftp_connect($this->host, $this->port, 30);
		if (!$this->fp) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return(true);
	}
	
	function login($user, $pass) {
		$php_errormsg = '';
		if (!@ftp_login($this->fp, $user, $pass)) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return(true);
	}
	
	function SetType($transfertype) {
		$this->transfertype = $transfertype;
		return(true);
	}
	
	function Passive($pasv) {
		$php_errormsg = '';
		if (!@ftp_pasv($this->fp, $pasv)) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return(true);
	}
	
	function pwd() {
		$php_errormsg = '';
		if (!($res = @ftp_pwd($this->fp))) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return($res);
	}
	
	function chdir($dir) {
		$php_errormsg = '';
		if (!@ftp_chdir($this->fp, $dir)) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return(true);
	}
	
	function nlist() {
		$php_errormsg = '';
		if (!($res = @ftp_nlist($this->fp, '.'))) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return($res);
	}
	
	function get($remotefile, $localfile) {
		$php_errormsg = '';
		if (!@ftp_get($this->fp, $localfile, $remotefile, $this->transfertype)) {
			$this->lastmsg = $php_errormsg;
			$this->PushError('get', "Unable to download $remotefile", $php_errormsg);
			return(false);
		}
		return(filesize($localfile));
	}

	function delete($remotefile) {
		$php_errormsg = '';
		if (!@ftp_delete($this->fp, $remotefile)) {
			$this->lastmsg = $php_errormsg;
			return(false);
		}
		return(true);
	}
	
	
	function quit() {
		if ($this->fp) {
			ftp_close($this->fp);
			$this->fp = NULL;
		}
		return(true);
	}

	
	function PushError($fctname, $msg, $desc=false){
		$error=array();
		$error['time']=time();
		$error['fctname']=$fctname;
		$error['msg']=$msg;
		$error['desc']=$desc;
		if($desc) $tmp=' ('.$desc.')'; else $tmp='';
		return(array_push($this->errors,$error));
	}
	
	function PopError(){
		if(count($this->errors)) return(array_pop($this->_error_array));
			else return(false);
	}

	
	
}
