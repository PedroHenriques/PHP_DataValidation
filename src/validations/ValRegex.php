<?php
/************************************************************
*															*
* PHP Data Validator v1.0.0									*
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

class ValRegex implements iValidation {
	public function runCheck($field, $input, array $params) {
		// make sure all required $param keys are set
		if (!isset($params[0])) {
			return(false);
		}

		if (preg_match($params[0], $input) === 1) {
			return(true);
		}else{
			return(false);
		}
	}
}

?>
