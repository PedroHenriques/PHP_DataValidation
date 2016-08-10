<?php
/************************************************************
*															*
* PHP Data Validator v1.0.1									*
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

class ValLwTh implements iValidation {
	public function runCheck($field, $input, array $params) {
		// make sure all required $param keys are set
		if (!isset($params[0])) {
			return(false);
		}

		if ((float)$input < (float)$params[0]) {
			return(true);
		}else{
			return(false);
		}
	}
}

?>
