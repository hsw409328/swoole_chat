<?php
date_default_timezone_set ( 'Asia/Shanghai' );
echo "author:Mr.hao [www.51hsw.com] \n email:409328820@qq.com \n start:" . time () . "\n";

require_once 'LogFile.php';
require_once 'UsersList.php';
class DanmuServer {
	public function __construct() {
		$serv = new Swoole\Websocket\Server ( "0.0.0.0", 9501 );
		
		$serv->on ( 'Open', [ 
				$this,
				"onOpen" 
		] );
		
		$serv->on ( 'Message', [ 
				$this,
				"onMessage" 
		] );
		
		$serv->on ( 'Close', [ 
				$this,
				"onClose" 
		] );
		
		$serv->start ();
	}
	public function onOpen($server, $req) {
		// $req->fd 是客户端id
		LogFile::writeAction ( date ( "Y-m-d H:i:s" ) . " 客户端：{$req->fd} 开始" );
		
		// 读取原来的聊天室的记录
		$_record = LogFile::readFileAction ( date ( "Ymd" ) . "_sns_record.log" );
		
		// 如果是第一次进入，欢迎语 不记录
		$_msg = "欢迎您的到来，基友社交！<br>" . str_replace ( "\n", "<br>", $_record );
		$server->push ( $req->fd, json_encode ( [ 
				'type' => 'list_msg',
				'rs' => $_msg 
		] ) );
	}
	public function onMessage($server, $frame) {
		// $frame->fd 是客户端id，$frame->data是客户端发送的数据
		// 服务端向客户端发送数据是用 $server->push( '客户端id' , '内容')
		$data = json_decode ( $frame->data, true );
		
		if (is_null ( $data )) {
			$this->onError ( $server, $frame );
		} else {
			$this->_switchMode ( $data ['code'], $data ['msg'], $server, $frame );
		}
	}
	private function _switchMode($_code, $msg, $server, $frame) {
		switch ($_code) {
			case 'all' :
				echo 'all';
				$this->_onOneToAll ( $server, $frame, $msg );
				break;
			case 'one' :
				echo 'one';
				$this->_onOenToOen ( $server, $frame, $msg );
				break;
			case 'init' :
				$this->_onInit ( $server, $frame, $msg );
				break;
			default :
				$this->onError ( $server, $frame );
		}
	}
	private function _onInit($server, $frame, $msg) {
		$usersListObject = new UsersList ( $server, $frame->fd, $msg );
		$usersListObject->saveUsersList ();
		$usersListObject->noticeUsersList ();
		$this->onError ( $server, $frame );
	}
	private function _onOneToAll($server, $frame, $msg) {
		LogFile::writeAction ( $msg . ' ' . date ( "Y-m-d H:i:s" ), date ( "Ymd" ) . "_sns_record.log" );
		foreach ( $server->connections as $fd ) {
			$server->push ( $fd, json_encode ( [ 
					'type' => 'list_msg',
					'rs' => $msg . ' ' . date ( "Y-m-d H:i:s" ) 
			] ) ); // 循环广播
		}
	}
	private function _onOenToOen($server, $frame, $msg) {
		$data = json_decode ( $frame->data, true );
		if (is_null ( $data )) {
			$this->onError ( $server, $frame );
		} else {
			$_tid = $data ['tid'];
			if ($server->exist ( $_tid )) {
				$server->push ( $_tid, json_encode ( [ 
						'type' => 'list_one_msg',
						'rs' => $msg,
						'tid' => $frame->fd 
				] ) );
			} else {
				$server->push ( $frame->fd, json_encode ( [ 
						'type' => 'list_one_close',
						'rs' => '对方已经退出聊天' 
				] ) );
			}
		}
	}
	public function onClose($server, $fd) {
		LogFile::writeAction ( date ( "Y-m-d H:i:s" ) . " 客户端 {$fd} 关闭" );
		
		$usersListObject = new UsersList ( $server, $fd, "" );
		$usersListObject->deleteUser ();
		$usersListObject->noticeUsersList ();
	}
	public function onError($server, $frame) {
		$server->push ( $frame->fd, json_encode ( [ 
				'type' => 'list_msg_error',
				'rs' => '消息发送失败 ' . date ( "Y-m-d H:i:s" ) 
		] ) );
	}
}

$obj = new DanmuServer ();

