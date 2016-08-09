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
namespace DataValidator\Support;

class ValidationErrors {
	// stores all the default error messages will be stored (populated from the JSON file)
	private $error_default_messages = [];
	// stores all the user defined error messages will be stored (populated "on the fly" by the user)
	// NOTE: custom messages have precedence over default messages if the same names are used
	private $error_custom_messages = [];
	// stores all the errors will be stored
	private $errors = [];
	// stores all the debug errors will be stored
	private $debug_errors = [];
	// stores any aliases for the data fields
	private $aliases = [];

	// will build the default error messages based on the error_msg.json file
	// the path to that JSON file can be given as a param to the constructor
	public function __construct($json_path = "") {
		// build the path to the JSON file
		$json_path = ($json_path !== "" ? $json_path : dirname(__DIR__)."/data/error_msg.JSON");

		// build the default error message list from the JSON file
		if (file_exists($json_path)) {
			$json_content = file_get_contents($json_path);

			if ($json_content !== false) {
				$this->error_default_messages = json_decode(utf8_encode($json_content), true);
			}
		}
	}

	// sets the aliases to be used for the messages
	public function setAliases(array $aliases) {
		// store the aliases
		$this->aliases = $aliases;
	}

	// clears all generated messages
	public function clearMessages() {
		$this->errors = [];
		$this->debug_errors = [];
	}

	// sets an error message for the given field and validation
	public function setError($field, $check_type, $input, array $params) {
		// validate the parameters
		if ($field === "" || $check_type === "") {
			return;
		}

		// find the error message, if one wasn't provided, first in the custom messages and then in the
		// default messages. If none exist use an empty string
		if (isset($this->error_custom_messages[$field][$check_type])) {
			$message = $this->error_custom_messages[$field][$check_type];
		}else if (isset($this->error_custom_messages[$check_type])) {
			$message = $this->error_custom_messages[$check_type];
		}else if (isset($this->error_default_messages[$check_type])) {
			$message = $this->error_default_messages[$check_type];
		}else{
			$message = "";
		}

		// extract "replaced_values" from the $params variable
		$params_replaced_values = [];
		if (isset($params["replaced_values"])) {
			$params_replaced_values = $params["replaced_values"];
			unset($params["replaced_values"]);
		}

		// replace any dynamic parts in the error message by the corresponding information in $params
		// first replace any keywords inside %%keyword%%
		// NOTE: if %%keyword%% can't be replaced, then %keyword% will be used instead
		while (preg_match("/%%([^%]+)%%/", $message, $regex_matches, PREG_OFFSET_CAPTURE) === 1) {
			// default value, in case %%keyword%% can't be found
			$dynamic_text = "%params[{$regex_matches[1][0]}]%";

			// if there are any replaced values make additional checks
			if (!empty($params_replaced_values)) {
				if ($regex_matches[1][0] === "...") {
					// display all the values that were replaced and add the keyword to add all the non-replaced values below
					// uses aliases if available

					// find any aliases for the relevant values
					$values_processed = [];
					foreach ($params_replaced_values as $value) {
						$dynamic_text[] = (isset($this->aliases[$value]) ? $this->aliases[$value] : $value);
					}

					// build the final dynamic_text string
					$dynamic_text = implode(", ", $values_processed)."%params[{$regex_matches[1][0]}!]%";
				}else if (isset($params_replaced_values[$regex_matches[1][0]])) {
					// replace the default $dynamic_text value by the desired replaced value
					// uses alias if available
					$dynamic_text = (isset($this->aliases[$params_replaced_values[$regex_matches[1][0]]]) ? $this->aliases[$params_replaced_values[$regex_matches[1][0]]] : $params_replaced_values[$regex_matches[1][0]]);
				}
			}

			// replace the matched keyword by the value in $params or "" if it doesn't exist
			$message = substr($message, 0, $regex_matches[0][1]).$dynamic_text.substr($message, $regex_matches[0][1] + strlen($regex_matches[0][0]));
		}

		// second replace any keywords inside %keyword%
		while (preg_match("/%([^%]+)%/", $message, $regex_matches, PREG_OFFSET_CAPTURE) === 1) {
			// determine the dynamic string
			if (preg_match("/^(\w+)\[([^\[\]]+)\]$/", $regex_matches[1][0], $var_name_matches) === 1) {
				// this keyword is in the form of an array
				// default value, in case the desired one can't be found
				$dynamic_text = "";

				// if the desired variable exists make additional checks
				if (isset(${$var_name_matches[1]})) {
					if ($var_name_matches[2] === "...") {
						// display all the values
						$dynamic_text = implode(", ", ${$var_name_matches[1]});
					}else if ($var_name_matches[2] === "...!") {
						// display all the values, except the ones that have a replaced equivalent
						// and were added before
						foreach (${$var_name_matches[1]} as $key => $value) {
							if (!isset($params_replaced_values[$key])) {
								$dynamic_text .= ", {$value}";
							}
						}
					}else if (isset(${$var_name_matches[1]}[$var_name_matches[2]])) {
						// display only the desired value
						$dynamic_text = ${$var_name_matches[1]}[$var_name_matches[2]];
					}
				}
			}else{
				// this keyword is already the variable name
				// process the cases where the keyword is "field" -> need to check for aliases
				if ($regex_matches[1][0] === "field" && isset($this->aliases[$field])) {
					$dynamic_text = $this->aliases[$field];
				}else{
					// either it isn't "field" or an alias doesn't exist, so do the standard processing
					$dynamic_text = (isset(${$regex_matches[1][0]}) ? ${$regex_matches[1][0]} : "");
				}
			}

			// replace the matched keyword by the value in $params or "" if it doesn't exist
			$message = substr($message, 0, $regex_matches[0][1]).$dynamic_text.substr($message, $regex_matches[0][1] + strlen($regex_matches[0][0]));
		}

		$this->errors[$field][] = $message;
	}

	// sets a debug error message
	public function setDebugError($message) {
		// validate the parameters
		if ($message === "") {
			return;
		}

		$this->debug_errors[] = $message;
	}

	// registers a custom error message "on the fly"
	public function addMessage($message, $check_type, $field = "") {
		// validate parameters
		if ($check_type === "") {
			return;
		}

		// store this validation in the instance
		if ($field === "") {
			// setting a new default message for a check type
			$this->error_custom_messages[$check_type] = $message;
		}else{
			// setting a message for a specific field and check type
			$this->error_custom_messages[$field][$check_type] = $message;
		}
	}

	// returns the error(s) for the given field and at the specified index
	// if no field is provided all errors for all fields will be returned
	// if just a field is provided all errors for that field will be returned
	// if a field and index are provided that specific error will be returned
	public function getErrors($field = "", $index = -1) {
		// fetch and return the requested error(s)
		if ($field === "") {
			// return all errors
			return($this->errors);
		}else if ($index === -1) {
			// return all errors for the specified field
			return(isset($this->errors[$field]) ? $this->errors[$field] : []);
		}else if (isset($this->errors[$field][$index])) {
			// return error for the specified field and index
			return($this->errors[$field][$index]);
		}else{
			// couldn't find the requested error, so return an empty string
			return;
		}
	}

	// returns the debug messages
	public function getDebugErrors() {
		return($this->debug_errors);
	}

	// returns the number of fields with errors
	public function getCount() {
		return(count($this->errors));
	}
}

?>
