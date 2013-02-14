<?php

class autoloader{

	private $directory;
	private $prefixLength;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __construct($baseDirectory = __DIR__){
		$this->directory    = $baseDirectory;
		$this->prefixLength = strlen($this->prefix);
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public static function register($prepend = true){
		spl_autoload_register(array(new self, 'autoload'), true, $prepend);
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function classFile($api){

		$first = substr(strtolower($api), 0, 4);

		if(strpos($api, ".php") !== false){
			$direct	= true;
			$class	= $api;
		}else
		if($first == 'core'){
			$class	= APP.'/module/core/core.'.substr(strtolower($api), 4).'.php';
			$alter	= USER.'/api/core.'.substr(strtolower($api), 4).'.php';
		}else
		if($first == 'data'){
			$parts	= array_map('strtolower', explode(' ', preg_replace('/(?!^)[[:upper:]]/',' \0', $api)));
			$file   = lcfirst(substr(implode('', array_map('ucfirst', $parts)), 4));

			$class	= APP.'/module/core/data.'.$file.'.php';
			$alter	= USER.'/api/data.'.$file.'.php';
		}else{
			$parts	= array_map('strtolower', explode(' ', preg_replace('/(?!^)[[:upper:]]/',' \0', $api)));
			$mod   	= $parts[0];

			if(count($parts) > 1){
				unset($parts[0]);
				$file   = $mod.implode('', array_map('ucfirst', $parts));

				$class	= APP.'/module/'.$mod.'/api.'.$file.'.php';
				$alter	= USER.'/module/'.$mod.'/api.'.$file.'.php';
				$custom	= USER.'/api/api.'.$file.'.php';
			}else{
				$class	= APP.'/module/'.$mod.'/api.'.$api.'.php';
				$alter	= USER.'/module/'.$mod.'/api.'.$api.'.php';
				$custom	= USER.'/api/api.'.$api.'.php';
			}
		}

		$alter	= (isset($custom) && file_exists($custom)) ? $custom : $alter;
		$class	= (isset($alter)  && file_exists($alter))  ? $alter	 : $class;

		return $class;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function autoload($api){

		$class = $this->classFile($api);

	//	if(file_exists($class)){
			try{
				require_once($class);
			}
			catch(Exception $e){
				throw new Exception("Class file not found : ".$api);
			}
	//	}

	}
}


