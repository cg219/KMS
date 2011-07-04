<?

/*

Author: Clemente Gomez
Handle: KreativeKing
Twitter: @Kreativeking - www.twitter.com/KreativeKing

*/

include_once('Logger.php');

class FileManagement {

	private $uploadPath;
	private $file;
	private $isLoggedIn = false;
	private $logger;
	private $conn;
	private $baseDir;
	private $mode = 'image';
	
	function __construct( $base ) {
		$this->logger = new Logger();
		$this->baseDir = $base;
	}
	
	function __deconstruct() {
		if($this->conn) {
			ftp_close($this->conn);
		}
	}
	
	function connect($server, $user, $pass, $isPassive = false) {
		$this->conn = ftp_connect($server);
		$loginResult = ftp_login($this->conn, $user, $pass);
		ftp_pasv($this->conn, $isPassive);
		
		if( (!$this->conn) || (!$loginResult) ) {
			
			$this->logger->log('File Management failed to connect');
			$this->logger->log('Attemped to connect to ' . $server . ', from user: ' . $user);
			$this->isLoggedIn = false;
			return false;
		}
		else {
			$this->logger->log('Connected to ' . $server . ', as user: ' . $user);
			$this->isLoggedIn = true;
			return true;
		}
	}
	
	function upload($from, $to, $name) {
		$types = $this->getTypeArray();
		$fileType = end(explode('.', $from));
		$name = '/' . $name;
		
		$ftpMode = ($types == 'ascii') ? FTP_ASCII : FTP_BINARY;
		
		if( in_array($fileType, $types) && $this->mode != null) {
			$upload = ftp_put($this->conn, $this->baseDir . $to . $name, $from, $ftpMode);
		}
		else {
			$this->logger->log('File is of invaliid type');
			return false;
		}
		
		if($upload) {
			$this->logger->log('File uploaded to ' . $to);
			return true;
		}
		else {
			$this->logger->log('File ' . $from . ' failed to upload to ' . $to);
			return false;
		}
	}
	
	function makeDirectory($directory) {
		if(!ftp_chdir($this->conn, BASE_DIR . $directory)) {
			if(ftp_mkdir($this->conn, BASE_DIR . $directory)) {
				$this->logger->log('Created Directory: ' . $directory . ' in ' . $this->baseDir);
				return true;
			}
			else {
				$this->logger->log('Failed to create Directory: ' . $directory . ' in ' . $this->baseDir);
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	function setMode($mode) {
		$mode = strtolower($mode);
		
		if($mode == 'image' || $mode == 'ascii' || $mode == 'all') {
			$this->mode = $mode;
			$this->logger-log('Mode Set: ' . $mode);
		}
		else {
			$this->logger-log($mode . ' is an invalid mode');
		}
	}
	
	private function getTypeArray() {
		switch($this->mode) {
			case 'image':
				return array('jpg', 'jpeg', 'png', 'gif', 'bmp');
				break;
			
			case 'ascii':
				return array('txt', 'csv', 'asc' );
				break;
			
			default:
				return null;
				break;
		}
	}
}

?>