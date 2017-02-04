<?php
// 
// pemftp - Class by Alexey Dotsenko <alex at paneuromedia dot com>
// http://www.phpclasses.org/browse/package/1743.html
// License: Free for non-commercial use
//


class ftp  extends ftp_base {
	function ftp($verb=FALSE, $le=FALSE) {
		$this->LocalEcho = $le;
		$this->Verbose = $verb;
		$this->ftp_base();
	}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Private functions                                                                 -->
// <!-- --------------------------------------------------------------------------------------- -->

	function _settimeout($sock) {
		if(!@stream_set_timeout($sock, $this->_timeout)) {
			$this->PushError('_settimeout','socket set send timeout');
			$this->_quit();
			return FALSE;
		}
		return TRUE;
	}

	function _connect($host, $port) {
		$this->SendMSG("NOT using PHP's socket extension");
		$this->SendMSG("Creating socket");
		$sock = @fsockopen($host, $port, $errno, $errstr, $this->_timeout);
		if (!$sock) {
			$this->PushError('_connect','socket connect failed', $errstr." (".$errno.")");
			return FALSE;
		}
		$this->_connected=true;
		return $sock;
	}

	function _readmsg($fnction="_readmsg"){
		if(!$this->_connected) {
			$this->PushError($fnction, 'Connect first');
			return FALSE;
		}
		$result=true;
		$this->_message="";
		$this->_code=0;
		$go=true;
		do {
			$tmp=@my_fgets($this->_ftp_control_sock, 512);
			if($tmp===false) {
				$go=$result=false;
				$this->PushError($fnction,'Read failed');
			} else {
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
		$status=@fputs($this->_ftp_control_sock, $cmd.CRLF);
		if($status===false) {
			$this->PushError($fnction,'socket write failed');
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
			$this->_ftp_data_sock=@fsockopen($this->_datahost, $this->_dataport, $errno, $errstr, $this->_timeout);
			if(!$this->_ftp_data_sock) {
				$this->PushError("_data_prepare","fsockopen fails", $errstr." (".$errno.")");
				$this->_data_close();
				return FALSE;
			}
			else $this->_ftp_data_sock;
		} else {
			$this->SendMSG("Only passive connections available!");
			return FALSE;
		}
		return TRUE;
	}

	function _data_read($mode=FTP_ASCII, $fp=NULL) {
		$NewLine=$this->NewLineCode[$this->OS_local];
		if(is_resource($fp)) $out=0;
		else $out="";
		if(!$this->_passive) {
			$this->SendMSG("Only passive connections available!");
			return FALSE;
		}
		if($mode!=FTP_BINARY) {
			while (!feof($this->_ftp_data_sock)) {
				$tmp=fread($this->_ftp_data_sock, 4096);
				$line.=$tmp;
				if(!preg_match("/".CRLF."$/", $line)) continue;
				$line=rtrim($line,CRLF).$NewLine;
				if(is_resource($fp)) $out+=fwrite($fp, $line, strlen($line));
				else $out.=$line;
				$line="";
			}
		} else {
			while (!feof($this->_ftp_data_sock)) {
				$block=fread($this->_ftp_data_sock, 4096);
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
			$this->SendMSG("Only passive connections available!");
			return FALSE;
		}
		if(is_resource($fp)) {
			while(!feof($fp)) {
				$line=my_fgets($fp, 4096);
				if($mode!=FTP_BINARY) $line=rtrim($line, CRLF).CRLF;
				do {
					if(($res=@fwrite($this->_ftp_data_sock, $line))===FALSE) {
						$this->PushError("_data_write","Can't write to socket");
						return FALSE;
					}
					$line=substr($line, $res);
				}while($line!="");
			}
		} else {
			if($mode!=FTP_BINARY) $fp=rtrim($fp, $NewLine).CRLF;
			do {
				if(($res=@fwrite($this->_ftp_data_sock, $fp))===FALSE) {
					$this->PushError("_data_write","Can't write to socket");
					return FALSE;
				}
				$fp=substr($fp, $res);
			}while($fp!="");
		}
		return TRUE;
	}

	function _data_close() {
		@fclose($this->_ftp_data_sock);
		$this->SendMSG("Disconnected data from remote host");
		return TRUE;
	}

	function _quit($force=FALSE) {
		if($this->_connected or $force) {
			@fclose($this->_ftp_control_sock);
			$this->_connected=false;
			$this->SendMSG("Socket closed");
		}
	}
}
?>