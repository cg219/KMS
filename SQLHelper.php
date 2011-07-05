<?

/*

Author: Clemente Gomez
Handle: KreativeKing
Twitter: @Kreativeking - www.twitter.com/KreativeKing

*/

	include_once('vo/KSQLObject.php');
	
	class SQLHelper
	{
		public $conn;
		public $results;
		
		function __construct( $server, $user, $pass, $db)
		{
			//$this->conn = new mysqli($server, $user, $pass, $db) or
			//die('Error in Connecting to Database');
			try {
				$this->conn = new PDO("mysql:host=$server;dbname=$db", $user, $pass);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $error) {
				echo 'Some Shit Happned';
			}
		}
		
		function __deconstruct() {
			if( $this->conn ) {
				//$this->conn->close();
				$this->conn = null;
			}
		}
		
		function selectAll( $table, $sqls ) {
			$obj = new stdClass();
			$obj->table = $table;
			$query = $this->createQuery('SELECT_ALL', $sqls, $obj);
			$results = array();
			$params = array();
			
			if($select = $this->conn->query($query)) {
				$select->setFetchMode(PDO::FETCH_ASSOC);
				
				while($row = $select->fetch()) {
					$results[] = $row;
				}
				
				$this->results = $results;
				
				return true;
			}
			else {
				return false;
			}
		}
		
		function insert( $table, $sqls ) {
			$obj = new stdClass();
			$obj->table = $table;
			$query = $this->createQuery('INSERT', $sqls, $obj);
			$params = $this->createParamArray($sqls);
			
			try {
				$insert = $this->conn->prepare($query);
				$insert->execute($params);
				$insert->closeCursor();
				return true;
			}
			catch(PDOException $error) {
				echo $error->getMessage();
				return false;
			}
		}
		
		private function createQuery($type, $sqls, $extras = null) {
			$query;
			$fields = $this->createFieldString($sqls);
			$values = $this->createValueString($sqls);
			$table;
			
			if($tables = $extras->table) {
				if(is_array($tables)) {
					
				}
				else {
					$table = $tables;
				}
			}
			
			switch ($type) {
				case 'INSERT':
					$query = 'INSERT INTO ' . $tables . ' ' . $fields . ' VALUES ' . $values;
					break;
					
				case 'SELECT_ALL':
					$query = 'SELECT * FROM ' . $table;
					break;
			}
			
			return $query;
		}
		
		private function bind_params($stmt, $sqls, $type = 'mysqli') {
			$types = $this->createTypeString($sqls);
			$params = $this->createParamArray($sqls);
			
			if($type == 'mysqli') {
				call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $params));
			}
			else {
				$types = $this->createTypeArray($sqls, 'pdo');
				
				for ($i = 1; $i <= count($params); $i++) {
					$stmt->bindValue($i, $params[$i], $types[$i]);
					//echo $types[$i];
				}
			}
			
		}
		
		//Needs Some Work
		
		/*private function bind_results($stmt) {
			$params = array();
			$results = array();
			$metadata = $stmt->result_metadata();
			
			foreach ($metadata->fetch_fields() as $field ) {
				$params[] = &$row[$field->name];
			}
			
			while($field = $metadata->fetch_field()) {
				$params[] = &$row[$field->name];
			}
			
			call_user_func_array('mysqli_stmt_bind_result', array_merge(array($stmt), $params));
			
			while( $stmt->fetch() ) {
				$x = array();
				foreach ($row as $key => $val) {
					$x[$key] = $val;
				}
				$results[] = $x;
			}
			
			echo '<pre>';
			print_r(PDO::getAvailableDrivers());
			echo '</pre>';
			
			return ' ';
		}*/
		
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
		
		private function createTypeArray($array, $type = 'mysqli') {
			$types = array();
			
			if( $type == 'mysqli' ) {
				foreach ($array as $sql ) {
					$types[] = $sql->getType();
				}
			}
			else {
				foreach ($array as $sql ) {
					$t;
					
					if( $sql->getType() == 's' ) {
						$t = PDO::PARAM_STR;
					}
					$types[] = $t;
				}
			}
			
			return $types;
		}
		
		private function createParamArray($array) {
			$params = array();
			
			foreach ($array as $sql ) {
				array_push($params, $sql->getVal());
			}
			
			return $params;
		}
	}
?>