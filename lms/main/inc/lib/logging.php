<?php

class SimpleLog {
	var $fp;
	var $logfile = 'zlms.log';
	var $loglevel = 5;
	var $nolog = false;


	function SimpleLog() {
		global $loglevel, $logfile;
		if (! empty ( $loglevel )) {
			if ($loglevel == 'fatal') $this->loglevel = 5;
			else if ($loglevel == 'error') $this->loglevel = 4;
			else if ($loglevel == 'warn') $this->loglevel = 3;
			else if ($loglevel == 'debug') $this->loglevel = 2;
			else if ($loglevel == 'info') $this->loglevel = 1;
		}
		if (! empty ( $logfile )) {
			$this->logfile = $logfile;
		}
		$this->fp = @ fopen ( $this->logfile, 'a+' );
		if (! $this->fp) {
			$this->nolog = true;
		}
	}


	function info($string) {
		if ($this->loglevel > 1 || $this->nolog) return;
		fwrite ( $this->fp, "info:[" . strftime ( "%Y-%m-%d %T" ) . "] $string\n" ) or die ( "Logger Failed to write to:" . $this->logfile );
	}


	function debug($string) {
		if ($this->loglevel > 2 || $this->nolog) return;
		fwrite ( $this->fp, "debug:[" . strftime ( "%Y-%m-%d %T" ) . "] $string\n" ) or die ( "Logger Failed to write to:" . $this->logfile );
	}


	function warn($string) {
		if ($this->loglevel > 3 || $this->nolog) return;
		fwrite ( $this->fp, "warn:[" . strftime ( "%Y-%m-%d %T" ) . "] $string\n" ) or die ( "Logger Failed to write to:" . $this->logfile );
	}


	function error($string) {
		if ($this->loglevel > 4 || $this->nolog) return;
		fwrite ( $this->fp, "error:[" . strftime ( "%Y-%m-%d %T" ) . "] $string\n" ) or die ( "Logger Failed to write to:" . $this->logfile );
	}


	function fatal($string) {
		if ($this->loglevel > 5) return;
		if ($this->nolog) die ( $string );
		fwrite ( $this->fp, "fatal:[" . strftime ( "%Y-%m-%d %T" ) . "] $string\n" ) or die ( "Logger Failed to write to:" . $this->logfile );
		die ( $string );
	}

}

?>
