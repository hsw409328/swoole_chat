<?php
class LogFile {
	public static function writeAction($msg, $filename = "", $_mode = "a+") {
		if (empty ( $filename )) {
			$hd_txt = self::createFileAction ( "log_" + date ( 'Ymd' ) + ".log", $_mode );
		} else {
			$hd_txt = self::createFileAction ( $filename, $_mode );
		}
		
		fwrite ( $hd_txt [0], $hd_txt [1] . "\n" . $msg );
		self::closeAction ( $hd_txt [0] );
	}
	public static function createFileAction($filename, $_mode) {
		$hd = fopen ( './' . $filename, $_mode );
		$_txt = fgets ( $hd );
		return [ 
				$hd,
				$_txt 
		];
	}
	public static function readFileAction($filename) {
		if (file_exists ( "./" . $filename )) {
			$rs = file_get_contents ( "./" . $filename );
		} else {
			$rs = "";
		}
		
		return $rs;
	}
	public static function closeAction($hd) {
		fclose ( $hd );
	}
}