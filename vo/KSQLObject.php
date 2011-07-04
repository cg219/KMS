<?

/*

Author: Clemente Gomez
Handle: KreativeKing
Twitter: @Kreativeking - www.twitter.com/KreativeKing

*/

class KSQLObject {
	
	private $name;
	private $val;
	private $type;
	private $field;
	
	function __construct($name, $field, $val, $type = 's') {
		$this->name = $name;
		$this->val = $val;
		$this->type = $type;
		$this->field = $field;
	}
	
	function __deconstruct() {
		
	}
	
	function getName() {
		return $this->name;
	}
	
	function getVal() {
		return $this->val;
	}
	
	function getType() {
		return $this->type;
	}
	
	function getField() {
		return $this->field;
	}
	
}

?>