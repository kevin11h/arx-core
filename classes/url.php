<?php defined('SYSPATH') or die('No direct script access.');

require_once DIR_CLASSES . DS . 'kohana/url.php';

class url extends Kohana_URL{}

class c_url extends Kohana_URL {

	public function redirect($uri = '', $protocol = NULL, $index = false){

		$url = str_replace(array('index.php/'), '', self::site($uri, $protocol, $index));

		if(!headers_sent()){
			header("Location: $url");
			exit();
		} else {
			die('<script type="text/javascript">document.location.href="'.$url.'"</script>');
		}
	}

}
