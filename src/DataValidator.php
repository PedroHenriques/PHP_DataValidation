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
namespace DataValidator;

// make sure the necessary files are required, in case the project where this
// library is inserted doesn't have an autoloader
require_once(dirname(__FILE__)."/interfaces/interfaces.php");
require_once(dirname(__FILE__)."/support/ValidationErrors.php");

use DataValidator\Interfaces\iValidator;
use DataValidator\Support\ValidationErrors;

// main class for this library
class DataValidator implements iValidator {
	// configures which checks are to be considered short-circuit
	// checks, as well as which values of $input will trigger the short-circuit.
	// These checks, when triggered, will stop all other checks from being run.
	private $short_circuit_rules = [
		["check" => "required", "input" => [null, ""], "run_check" => true],
		["check" => "!required", "input" => [null, ""], "run_check" => false]
	];

	// stores the array with the data to be validated
	private $data = [];
	// stores the validations to be applied to the data
	private $validations = [];
	// stores an instance of ValidationErrors class
	private $errors = null;
	// flags whether all checks, for each filed, should be run
	// or if 1 failed check stops further checks for that field
	private $single_fail = false;
	// stores the path to the "error_msg.json" file
	private $json_path = "";

	// stores any custom validation rules set by the user "on the fly"
	// the KEYS are the names of the validations and the VALUES are functions executing the respective validation
	// These function should return TRUE if the validation passed and FALSE otherwise
	// NOTE: custom validations have precedence over default validations if the same names are used
	private $custom_validations = [];

	// stores all objects of validation classes already instantiated
	private $val_class_objs = [];

	// receives an optional string with the path to the "error_msg.json" file
	public function __construct($json_path = "") {
		// store the parameter in the instace variable
		$this->json_path = $json_path;

		// create an instance of the ValidationErrors class
		$this->errors = new ValidationErrors($this->json_path);
	}

	// execute the validation of $data using the information in $validations
	// receives an array with the data to be validated and another array with the validations
	// optional parameter: boolean to control whether 1 failed check should stop further checks for that field
	public function validate(array $data, array $validations, $single_fail = false) {
		// store the parameters in the instace variables
		$this->data = $data;
		$this->validations = $validations;
		$this->single_fail = (bool)$single_fail;

		// run the preparation tasks to get the information ready for use
		$this->prepTasks();

		// loop through each $validations entry and execute the necessary checks
		foreach ($this->validations as $field => $checks) {
			// grab this field's $data
			$input = (isset($this->data[$field]) ? $this->data[$field] : null);

			// loop through the short-circuit rules
			foreach ($this->short_circuit_rules as $rule_data) {
				// determine if this short-circuit is when the check is set or not
				if (substr($rule_data["check"], 0, 1) === "!") {
					$check_present = false;
					$check_id = substr($rule_data["check"], 1);
				}else{
					$check_present = true;
					$check_id = $rule_data["check"];
				}

				// see if this check is present for this field
				$check_exists = false;
				foreach ($checks as $check) {
					if (preg_match("/^".$check_id.";?/i", $check) === 1) {
						// found the check: flag it as existing and exit loop
						$check_exists = true;
						break;
					}
				}

				// if the patterns match process it
				if ($check_present === $check_exists) {
					// see if $input has one of the values that trigger the short-circuit
					if (in_array($input, $rule_data["input"])) {
						// if the check will be run prepare the data
						// else move on
						if ((bool)$rule_data["run_check"]) {
							// reduce $checks to the short-circuit $check and move on to the loop
							// below that actually tests the check and builds the error message
							$checks = [$check];
							break;
						}else{
							// no error message so move on to the next option
							continue(2);
						}
					}
				}
			}

			// loop through each $checks and process each one
			foreach ($checks as $check) {
				// split the $check into the check type and extra params
				$check_parts = explode(";", $check);

				// sanity check
				if (empty($check_parts)) {
					// the check information isn't usable
					// set the debug error message
					$this->errors->setDebugError("The {$check} validation option for {$field} field is not usable. [field: {$field} | option: {$check} | input: {$input}]");

					// move on to next $check
					continue;
				}

				// determine the check type
				$check_type = array_shift($check_parts);

				// local variable to store the validation's class name
				$class_name = "";

				// see if this check_type exists in the custom validations
				if (!isset($this->custom_validations[$check_type])) {
					// it doesn't, so use the default validations
					// determine the class name for this check type
					$class_name = $this->createClassName($check_type);

					// make sure the desired class file exists
					$class_file_path = dirname(__FILE__)."/validations/{$class_name}.php";
					if (!file_exists($class_file_path)) {
						// couldn't find the class file
						// set the debug error message
						$this->errors->setDebugError("The {$class_name}.php file couldn't be found. [field: {$field} | option: {$check} | input: {$input}]");
					}else{
						// include the desired class file
						include_once($class_file_path);
					}

					// make sure the desired class is defined
					$class_path = "DataValidator\\Validations\\{$class_name}";
					if (!class_exists($class_path)) {
						// the class is not defined, so move on
						// set the debug error message
						$this->errors->setDebugError("The {$class_name} class isn't defined. [field: {$field} | option: {$check} | input: {$input}]");
						continue;
					// it is, check if we already have an instance of the class
					}else if (!isset($this->val_class_objs[$check_type])) {
						// we don't, create an instance and store it
						$this->val_class_objs[$check_type] = new $class_path;
					}
				}

				// array used to store extra parameters to be used in the validation
				$validation_params = [];

				// loop through each remaining $check_parts and look for any entry
				// that needs to be replaced by one of $this->data entries
				foreach ($check_parts as $value) {
					// check if this value needs to be replaced
					// if it is replaced, a copy of the replaced value will be stored in $validation_params
					// under $validation_params["replaced_values"][$key]
					$replaced_value = "";
					if (preg_match("/^%(.*)%$/", $value, $regex_matches)) {
						$value = (isset($this->data[$regex_matches[1]]) ? $this->data[$regex_matches[1]] : null);
						$replaced_value = $regex_matches[1];
					}

					// store this information
					$validation_params[] = $value;

					// if we replaced a keyword, store it in $validation_params
					if ($replaced_value !== "") {
						$validation_params["replaced_values"][count($validation_params) - 1] = $replaced_value;
					}
				}

				// run the validation, either from the custom validations or the default ones
				if (($class_name === "" && !(bool)call_user_func($this->custom_validations[$check_type], $field, $input, $validation_params)) || ($class_name !== "" && !(bool)$this->val_class_objs[$check_type]->runCheck($field, $input, $validation_params))) {
					// store the error for this failed validation
					$this->errors->setError($field, $check_type, $input, $validation_params);

					// TODO: remove this debug info
					print_r("<p style='color:red;'>validation for {$field} field and {$check} check FAILED!</p>");

					// if "1 fail only" is active, move on to the next field
					if ($this->single_fail) {
						continue(2);
					}
				}else{
					// TODO: remove this debug info
					print_r("<p style='color:green;'>validation for {$field} field and {$check} check passed!</p>");
				}
			}
		}
	}

	// run all the necessary tasks to reach a state where the validate() method can do its job
	private function prepTasks() {
		// loop through each $validations entry and build the array with the field's alias
		// the keys are the actual field names and the values are the respective aliases
		$validations_processed = [];
		$field_aliases = [];
		foreach ($this->validations as $field => $checks) {
			// separate the field name from the field alias
			$field_data = explode(";", $field);

			// if no alias was provided for this field, move on
			if (!isset($field_data[0]) || !isset($field_data[1])) {
				// keep this entry as is
				$validations_processed[$field] = $checks;
				continue;
			}

			// store the information
			$validations_processed[$field_data[0]] = $checks;
			$field_aliases[$field_data[0]] = $field_data[1];
		}

		// update this instance's validations
		$this->validations = $validations_processed;

		// register the aliases with the error message handler
		$this->errors->setAliases($field_aliases);

		// clear any previously generated error messages
		$this->errors->clearMessages();
	}

	// return TRUE if all validations passed, FALSE if at least 1 failed
	public function passed() {
		if ($this->errors->getCount() === 0) {
			return(true);
		}else{
			return(false);
		}
	}

	// return TRUE if at least 1 validations failed, FALSE if none failed
	public function failed() {
		if ($this->errors->getCount() !== 0) {
			return(true);
		}else{
			return(false);
		}
	}

	// registers a custom validation "on the fly"
	public function addValidation($check_type, callable $function) {
		// validate parameters
		if ($check_type === "") {
			return;
		}

		// store this validation in the instance
		$this->custom_validations[$check_type] = $function;
	}

	// registers a new short-circuit rule
	public function addShortCircuit($check, array $input_values, $run_check) {
		// validate inputs
		if (!is_string($check) || $check === "" || empty($input_values)) {
			// invalid parameters
			return;
		}

		// type cast parameters
		$run_check = (bool)$run_check;

		// store this rule
		$this->short_circuit_rules[] = [
			"check" => $check,
			"input" => $input_values,
			"run_check" => $run_check
		];
	}

	// returns the error(s) for the given field and at the specified index
	// if no field is provided all errors for all fields will be returned
	// if just a field is provided all errors for that field will be returned
	// if a field and index are provided that specific error will be returned
	public function getErrors($field = "", $index = -1) {
		return($this->errors->{__FUNCTION__}($field, $index));
	}

	// registers a custom error message "on the fly"
	public function addMessage($message, $check_type, $field = "") {
		$this->errors->{__FUNCTION__}($message, $check_type, $field);
	}

	// returns the debug error messages
	public function getDebugErrors() {
		return($this->errors->{__FUNCTION__}());
	}

	// determines the class name for the given $check
	private function createClassName($check) {
		// character used as word delimeter
		$word_delimiter = "_";

		// start by converting all characters to lower case
		$class_name = strtolower($check);
		// build the final name for this class
		$class_name = "Val".str_replace($word_delimiter, "", ucwords(ucfirst($class_name), $word_delimiter));

		return($class_name);
	}
}

?>
