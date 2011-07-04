<?
	include_once('vo/KSQLObject.php');
	
	class SQLHelper
	{
		public $conn;
		
		function __construct( $server, $user, $pass, $db)
		{
			$this->conn = new mysqli($server, $user, $pass, $db) or
			die('Error in Connecting to Database');
		}
		
		function __deconstruct() {
			if( $this->conn ) {
				$this->conn->close();
			}
		}
		
		function verifyCreds( $user, $pass, $table )
		{
			$query = "SELECT * FROM ? WHERE username = ? AND password = ? LIMIT 1";
			
			if($stmt = $this->conn->prepare( $query ))
			{
				$stmt->bind_param( 'sss', $table, $user, $pass );
				$stmt->execute();
				
				if( $stmt->fetch() )
				{
					$stmt->close();
					return true;
				}
			}
		}
		
		function insert( $table, $sqls ) {
			$fields = $this->createFieldString($sqls);
			$values = $this->createValueString($sqls);
			$types = $this->createTypeString($sqls);
			$params = $this->createParamString($sqls);
			$query = 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ' . $values;
			
			if($insert = $this->conn->prepare($query)) {
				call_user_func_array('mysqli_stmt_bind_param', array_merge(array($insert, $types), $params));
				$insert->execute();
				$insert->close();
				return true;
			}
			else {
				return false;
			}
		}
		
		private function createFieldString($array) {
			$str = '(';
			
			foreach ($array as $sql ) {
				$str .= $sql->getField() . ', ';
			}
			$str = substr_replace($str, ')', -2, 1);
			
			return $str;
		}
		
		private function createValueString($array) {
			$str = '(';
			
			foreach ($array as $sql ) {
				$str .= '?, ';
			}
			$str = substr_replace($str, ')', -2, 1);
			
			return $str;
		}
		
		private function createTypeString($array) {
			$str;
			
			foreach ($array as $sql ) {
				$str .= $sql->getType();
			}
			
			return $str;
		}
		
		private function createParamString($array) {
			$params = array();
			
			foreach ($array as $sql ) {
				array_push($params, $sql->getVal());
			}
			
			return $params;
		}
	}
?>