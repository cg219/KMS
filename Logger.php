<?

/*

Author: Clemente Gomez
Handle: KreativeKing
Twitter: @Kreativeking - www.twitter.com/KreativeKing

*/

class Logger
{
	private $currentMessage;
	private $history;
	
	public function __construct()
	{
		$this->history = array();
	}
	
	public function log( $message )
	{
		$currentMessage = $message;
		array_push( $this->history, $message );
	}
	
	public function clearHistory()
	{
		$this->history = array();
	}
}
?>