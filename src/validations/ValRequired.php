<?php
/************************************************************
*															*
* PHP Data Validator v1.0.2									*
*															*
* Copyright 2016, PedroHenriques 							*
* http://www.pedrojhenriques.com 							*
* https://github.com/PedroHenriques 						*
*															*
* Free to use under the MIT license.			 			*
* http://www.opensource.org/licenses/mit-license.php 		*
*															*
************************************************************/
namespace DataValidator\Validations;

use DataValidator\Interfaces\iValidation;

class ValRequired implements iValidation {
	public function runCheck($field, $input, array $params) {
		if ($input !== null && $input !== "") {
			return(true);
		}else{
			return(false);
		}
	}
}

?>
