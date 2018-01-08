<?php
// 
// pemftp - Class by Alexey Dotsenko <alex at paneuromedia dot com>
// http://www.phpclasses.org/browse/package/1743.html
// License: Free for non-commercial use
//


class ftp extends ftp_base {
	function ftp($verb=FALSE, $le=FALSE) {
		$this->LocalEcho = $le;
		$this->Verbose = $verb;
		$this->ftp_base(TRUE);
	}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Private functions                                                                 -->
// <!-- --------------------------------------------------------------------------------------- -->

	function _settimeout($sock) {
		if(!@socket_set_option($sock, 1, SO_RCVTIMEO, array("sec"=>$this->_timeout, "usec"=>0))) {
			$this->PushError('_connect','socket set receive timeout',socket_strerror(socket_last_error($sock)));
			@socket_close($sock);
			return FALSE;
		}
		if(!@socket_set_option($sock, 1, SO_SNDTIMEO, array("sec"=>$this->_timeout, "usec"=>0))) {
			$this->PushError('_connect','socket set send timeout',socket_strerror(socket_last_error($sock)));
			@socket_close($sock);
			return FALSE;
		}
		return true;
	}

	function _connect($host, $port) {
		$this->SendMSG("Using PHP's socket extension");
		$this->SendMSG("Creating socket");
		$sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($sock < 0) {
			$this->PushError('_connect','socket create failed',socket_strerror(socket_last_error($sock)));
			return FALSE;
		}
		if(!$this->_settimeout($sock)) return FALSE;
		$this->SendMSG("Connecting to \"".$host.":".$port."\"");
		if (!($res = @socket_connect($sock, $host, $port))) {
			$this->PushError('_connect','socket connect failed',socket_strerror(socket_last_error($sock)));
			@socket_close($sock);
			return FALSE;
		}
		$this->_connected=true;
		return $sock;
	}

	function _readmsg($fnction="_readmsg"){
		if(!$this->_connected) {
			$this->PushError($fnction,'Connect first');
			return FALSE;
		}
		$result=true;
		$this->_message="";
		$this->_code=0;
		$go=true;
		do {
			$tmp=@socket_read($this->_ftp_control_sock, 4096, PHP_BINARY_READ);
			if($tmp===false) {
				$go=$result=false;
				$this->PushError($fnction,'Read failed', socket_strerror(socket_last_error($this->_ftp_control_sock)));
			} elseif($tmp=="") $go=false;
			else {
				$this->_message.=$tmp;
//				for($i=0; $i<strlen($this->_message); $i++)
//					if(ord($this->_message[$i])<32) echo "#".ord($this->_message[$i]); else echo $this->_message[$i];
//				echo CRLF;
				if(preg_match("/^([0-9]{3})(-(.*".CRLF.")+\\1)? [^".CRLF."]+".CRLF."$/", $this->_message, $regs)) $go=false;
			}
		} while($go);
		if($this->LocalEcho) echo "GET < ".rtrim($this->_message, CRLF).CRLF;
		$this->_code=(int)$regs[1];
		return $result;
	}

	function _exec($cmd, $fnction="_exec") {
		if(!$this->_ready) {
			$this->PushError($fnction,'Connect first');
			return FALSE;
		}
		if($this->LocalEcho) echo "PUT > ",$cmd,CRLF;
		$status=@socket_write($this->_ftp_control_sock, $cmd.CRLF);
		if($status===false) {
			$this->PushError($fnction,'socket write failed', socket_strerror(socket_last_error($this->stream)));
			return FALSE;
		}
		$this->_lastaction=time();
		if(!$this->_readmsg($fnction)) return FALSE;
		return TRUE;
	}

	function _data_prepare($mode=FTP_ASCII) {
		if($mode==FTP_BINARY) {
			if(!$this->_exec("TYPE I", "_data_prepare")) return FALSE;
		} else {
			if(!$this->_exec("TYPE A", "_data_prepare")) return FALSE;
		}
		$this->SendMSG("Creating data socket");
		$this->_ftp_data_sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->_ftp_data_sock < 0) {
			$this->PushError('_data_prepare','socket create failed',socket_strerror(socket_last_error($this->_ftp_data_sock)));
			return FALSE;
		}
		if(!$this->_settimeout($this->_ftp_data_sock)) {
			$this->_data_close();
			return FALSE;
		}
		if($this->_passive) {
			if(!$this->_exec("PASV", "pasv")) {
				$this->_data_close();
				return FALSE;
			}
			if(!$this->_checkCode()) {
				$this->_data_close();
				return FALSE;
			}
			$ip_port = explode(",", ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*".CRLF."$", "\\1", $this->_message));
			$this->_datahost=$ip_port[0].".".$ip_port[1].".".$ip_port[2].".".$ip_port[3];
            $this->_dataport=(((int)$ip_port[4])<<8) + ((int)$ip_port[5]);
			$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
			if(!@socket_connect($this->_ftp_data_sock, $this->_datahost, $this->_dataport)) {
				$this->PushError("_data_prepare","socket_connect", socket_strerror(socket_last_error($this->_ftp_data_sock)));
				$this->_data_close();
				return FALSE;
			}
			else $this->_ftp_temp_sock=$this->_ftp_data_sock;
		} else {
			if(!@socket_getsockname($this->_ftp_control_sock, $addr, $port)) {
				$this->PushError("_data_prepare","can't get control socket information", socket_strerror(socket_last_error($this->_ftp_control_sock)));
				$this->_data_close();
				return FALSE;
			}
			if(!@socket_bind($this->_ftp_data_sock,$addr)){
				$this->PushError("_data_prepare","can't bind data socket", socket_strerror(socket_last_error($this->_ftp_data_sock)));
				$this->_data_close();
				return FALSE;
			}
			if(!@socket_listen($this->_ftp_data_sock)) {
				$this->PushError("_data_prepare","can't listen data socket", socket_strerror(socket_last_error($this->_ftp_data_sock)));
				$this->_data_close();
				return FALSE;
			}
			if(!@socket_getsockname($this->_ftp_data_sock, $this->_datahost, $this->_dataport)) {
				$this->PushError("_data_prepare","can't get data socket information", socket_strerror(socket_last_error($this->_ftp_data_sock)));
				$this->_data_close();
				return FALSE;
			}
			if(!$this->_exec('PORT '.str_replace('.',',',$this->_datahost.'.'.($this->_dataport>>8).'.'.($this->_dataport&0x00FF)), "_port")) {
				$this->_data_close();
				return FALSE;
			}
			if(!$this->_checkCode()) {
				$this->_data_close();
				return FALSE;
			}
		}
		return TRUE;
	}

	function _data_read($mode=FTP_ASCII, $fp=NULL) {
		$NewLine=$this->NewLineCode[$this->OS_local];
		if(is_resource($fp)) $out=0;
		else $out="";
		if(!$this->_passive) {
			$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
			$this->_ftp_temp_sock=socket_accept($this->_ftp_data_sock);
			if($this->_ftp_temp_sock===FALSE) {
				$this->PushError("_data_read","socket_accept", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
				$this->_data_close();
				return FALSE;
			}
		}
		if($mode!=FTP_BINARY) {
			while(($tmp=@socket_read($this->_ftp_temp_sock, 8192, PHP_NORMAL_READ))!==false) {
				$line.=$tmp;
				if(!preg_match("/".CRLF."$/", $line)) continue;
				$line=rtrim($line,CRLF).$NewLine;
				if(is_resource($fp)) $out+=fwrite($fp, $line, strlen($line));
				else $out.=$line;
				$line="";
			}
		} else {
			while($block=@socket_read($this->_ftp_temp_sock, 8192, PHP_BINARY_READ)) {
				if(is_resource($fp)) $out+=fwrite($fp, $block, strlen($block));
				else $out.=$line;
			}
		}
		return $out;
	}

	function _data_write($mode=FTP_ASCII, $fp=NULL) {
		$NewLine=$this->NewLineCode[$this->OS_local];
		if(is_resource($fp)) $out=0;
		else $out="";
		if(!$this->_passive) {
			$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
			$this->_ftp_temp_sock=socket_accept($this->_ftp_data_sock);
			if($this->_ftp_temp_sock===FALSE) {
				$this->PushError("_data_write","socket_accept", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
				$this->_data_close();
				return FALSE;
			}
		}
		if(is_resource($fp)) {
			while(!feof($fp)) {
				$line=my_fgets($fp, 4096);
				if($mode!=FTP_BINARY) $line=rtrim($line, CRLF).CRLF;
				do {
					if(($res=@socket_write($this->_ftp_temp_sock, $line))===FALSE) {
						$this->PushError("_data_write","socket_write", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
						return FALSE;
					}
					$line=substr($line, $res);
				}while($line!="");
			}
		} else {
			if($mode!=FTP_BINARY) $fp=rtrim($fp, $NewLine).CRLF;
			do {
				if(($res=@socket_write($this->_ftp_temp_sock, $fp))===FALSE) {
					$this->PushError("_data_write","socket_write", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
					return FALSE;
				}
				$fp=substr($fp, $res);
			}while($fp!="");
		}
		return TRUE;
	}

	function _data_close() {
		@socket_close($this->_ftp_temp_sock);
		@socket_close($this->_ftp_data_sock);
		$this->SendMSG("Disconnected data from remote host");
		return TRUE;
	}

	function _quit() {
		if($this->_connected) {
			@socket_close($this->_ftp_control_sock);
			$this->_connected=false;
			$this->SendMSG("Socket closed");
		}
	}
}
?>