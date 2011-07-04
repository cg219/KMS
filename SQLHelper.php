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
		
		function __construct( $server, $user, $pass, $db)
		{
			$this->conn = new mysqli($server, $user, $pass, $db);
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
		
		function selectAll( $table, $sqls ) {
			$obj = new stdClass();
			$obj->table = $table;
			$query = $this->createQuery('SELECT_ALL', $sqls, $obj);
			$results;
			
			if($select = $this->conn->prepare($query)) {
				$select->execute();
				$results = $this->bind_results($select);
				
				/*while($select->fetch()) {
					$data = array();
					foreach ( as  => ) {
					
					}
				}*/
				
				return true;
			}
			else {
				return false;
			}
		}
		
		function insert( $table, $sqls ) {
			$query = $this->createQuery('INSERT', $sqls);
			
			if($insert = $this->conn->prepare($query)) {
				$this->bind_params($insert, $sqls);
				$insert->execute();
				$insert->close();
				return true;
			}
			else {
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
		
		private function bind_params($stmt, $sqls) {
			$types = $this->createTypeString($sqls);
			$params = $this->createParamString($sqls);
			
			call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $params));
		}
		
		private function bind_results($stmt) {
			$params = array();
			$results = array();
			$metadata = $stmt->result_metadata();
			
			/*foreach ($metadata->fetch_fields() as $field ) {
				$params[] = &$row[$field->name];
			}*/
			
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
			print_r($results);
			echo '</pre>';
			
			return ' ';
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