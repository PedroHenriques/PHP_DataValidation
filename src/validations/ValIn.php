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

class ValIn implements iValidation {
	public function runCheck($field, $input, array $params) {
		// make sure $params has, at least, 1 value
		if (empty($params)) {
			return(false);
		}

		// check if the input is one of the valid values
		if (in_array($input, $params)) {
			return(true);
		}else{
			return(false);
		}
	}
}

?>
