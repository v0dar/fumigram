<?php 
/**
 * Help class for the help center
 */
class help extends users{
	public $uis = array();
	public $currp = 'home';
	public function Senction($path = ""){
		global $help,$context,$config,$me;
		$help = $this;
		$path  = self::secure($path);
	    $ui  = "help/uis/$path.phtml";
	    if (!file_exists($ui)) {
	        die("File not Exists : $ui");
	    }
	    $echo = '';
	    ob_start();
	    require($ui);
	    $echo = ob_get_contents();
	    ob_end_clean();
	    return $echo;
	}
	public function activeMenu($ui = 'home'){
		if ($ui == $this->currp) {
			return 'active';
		} elseif (in_array($ui, array_keys($this->uis)) && in_array($this->currp, $this->uis[$ui])) {
			return 'active';
		} elseif ($ui == $this->currp) {
			return 'active';
		}
	}
}