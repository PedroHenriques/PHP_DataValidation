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

class ValDate implements iValidation {
	public function runCheck($field, $input, array $params) {
		if ($input instanceof DateTime) {
            return(true);
        }

        if (strtotime($input) === false) {
            return(false);
        }

        $date = date_parse($input);

		if ($date === false) {
			return(false);
		}else{
			return(checkdate($date["month"], $date["day"], $date["year"]));
		}
	}
}

?>
