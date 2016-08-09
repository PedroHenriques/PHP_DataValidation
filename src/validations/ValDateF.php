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

class ValDateF implements iValidation {
	public function runCheck($field, $input, array $params) {
		// if no formats were provided
		if (empty($params)) {
			return(false);
		}

		$timestamp = strtotime($input, 0);
		if ($timestamp === false) {
			return(false);
		}

		foreach ($params as $format) {
			$formated_date = date($format, $timestamp);
			if ($formated_date !== false && $input === $formated_date) {
				return(true);
			}
		}

		// at this point none of provided formats matched the date
		return(false);
	}
}

?>
