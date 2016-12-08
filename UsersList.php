<?php
class UsersList {
	public $_serv, $_fd, $_uname;
	public function __construct($serv, $fd, $uname) {
		$this->_serv = $serv;
		$this->_fd = $fd;
		$this->_uname = $uname;
	}
	public function saveUsersList() {
		$rs = LogFile::readFileAction ( "sns_users_list.list" );
		
		if (empty ( $rs )) {
			$rs = [ ];
			$this->_addUsersList ( $rs );
		} else {
			$rs = str_replace ( "\n", "", $rs );
			$rs = json_decode ( $rs, true );
			$this->_addUsersList ( $rs );
		}
	}
	private function _addUsersList($oldRs) {
		$_key = $this->_fd.'_key';
		
		$rs = [ 
				$_key => [ 
						$this->_fd,
						$this->_uname 
				] 
		];
		$newRs = array_merge ( $oldRs, $rs );
		
		LogFile::writeAction ( json_encode ( $newRs ), "sns_users_list.list", "w+" );
	}
	public function getUsersList() {
		$rs = LogFile::readFileAction ( "sns_users_list.list" );
		if (empty ( $rs )) {
			$rs = [ ];
		} else {
			$rs = str_replace ( "\n", "", $rs );
			$rs = json_decode ( $rs, true );
		}
		return $rs;
	}
	public function deleteUser() {
		$rs = LogFile::readFileAction ( "sns_users_list.list" );
		$_key = $this->_fd.'_key';
		if (empty ( $rs )) {
			$rs = json_encode ( [ ] );
		} else {
			$rs = str_replace ( "\n", "", $rs );
			$rs = json_decode ( $rs, true );
			unset ( $rs [$_key] );
			LogFile::writeAction ( json_encode ( $rs ), "sns_users_list.list", "w+" );
		}
		return $rs;
	}
	// 通知全部用户列表更新
	public function noticeUsersList() {
		$rs = $this->getUsersList ();
		foreach ( $rs as $fd=>$v ) {
			$this->_serv->push ( $v[0], json_encode ( [ 
					'type' => 'users_list',
					'rs' => $rs 
			] ) );
		}
	}
}