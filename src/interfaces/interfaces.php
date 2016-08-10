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
namespace DataValidator\Interfaces;

// used by the validation classes
interface iValidation {

	// this is the method that will be called to run the validation
	// should return True if the validation passed and False if it failed
	public function runCheck($field, $input, array $params);
}

// used by the main class of the library
interface iValidator {
	public function validate(array $data, array $validations, $single_fail);
	public function passed();
	public function failed();
	public function addValidation($check_type, callable $function);
	public function addShortCircuit($check, array $input_values, $run_check);
	public function getErrors($field, $index);
	public function addMessage($message, $check_type, $field);
	public function getDebugErrors();
}

?>
