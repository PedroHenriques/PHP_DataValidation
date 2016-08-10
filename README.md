# PHP Data Validator - Easy to Use and Customizable

A PHP library that allows for an easy and customizable way to validate data.  

The code will run through an array of data and for each item will execute the specified validations. In the end any validations that failed will generate an error message that you can display.  

All the validations and error messages can be customized, even the default ones, and you can create new ones "on the fly" very easily.  

## Instructions

### Setup

#### => Composer:

Add the following to your project's `composer.json` file:
```
{
    "require": {
        "pedrohenriques/data-validator": "1.*"
    }
}
```  
Replacing the version with your preference. 

#### => Manual:

1. Copy the `src` folder into your project.
2. Reference the `DataValidator.php` file on the webpages you wish to use the Data Validator using, for example, `require_once("path/to/DataValidator.php")`.

<br>
### Using the Data Validator

In order to use the Data Validator create an instance of the DataValidator class using the code:  
```
use DataValidator\DataValidator;

$validator = new DataValidator([string $json_path]);
```  

With:  
- **$json_path**: String with the path to the `error_msg.json` file. This file contains the default error messages for each validation type.  
If a path isn't provided the code will assume that the file is located at `src/data/error_msg.json`.

<br>
Once the class has been instantiated, the code `$validator->validate(array $data, array $validations[, $single_fail]);` will execute the validations and generate any error messages.  

With:   
- **$data**: Array with the data to be validated.<br><br>
- **$validations**: Array with the desired validations for each of the $data entries (see below for the syntax).<br><br>
- **$single_fail**: Boolean that controls whether all validations for a $data entry should be executed, or if they should stop when 1 fails.  
`True` will stop a $data entry's validations as soon as 1 fails and `False` will execute all validations for a $data entry, regardless of how many fail.  
By default the code will assume the value `False`.

<br>
#### => `$validations` parameter syntax:  

The complete syntax is `$validations = ["field;alias" => ["check_type1;value1;value2...", "check_type2;value1;value2...", ...], ...]` where:  

- **field**: The name of the desired $data entry. The names must match in both the $data and $validations array.<br><br>
- **alias**: [optional] The alias for the field.  
If provided, the alias will be used in the error messages instead of the field name.  
If not provided, the field name will be used in the error messages.<br><br>
- **check_type**: The validation name for the check in question.<br><br>
- **value**: One value that will be made available to the validation code and the error messages.  
If the desired value is one of the $data entries use the format `%field%`, which will be replaced by that field's $data entry.

<br>
#### => Default Validations:  

This repository comes with the following validations available:  

Check Type | Description | Values
--- | --- | ---
`date` | Checks if the $data entry is a valid date, according to any of the PHP accepted formats | none
`date_f` | Checks if the $data entry is a valid date, according to specific formats | the accepted formats separated by `;`<br><br>The formats should follow PHP's date format
`email` | Checks if the $data entry is a valid email format | none
`gr_eq_th` | Checks if the $data entry is greater or equal than the validation value (as floats) | the number representing the floor for the check
`gr_th` | Checks if the $data entry is greater than the validation value (as floats) | the number representing the floor for the check
`in` | Checks if the $data entry is one of the validation values (as strings) | the desired values separated by `;`
`lw_eq_th` | Checks if the $data entry is lower or equal than the validation value (as floats) | the number representing the ceiling for the check
`lw_th` | Checks if the $data entry is lower than the validation value (as floats) | the number representing the ceiling for the check
`match` | Checks if the $data entry matches the validation value (as strings) | The value to match against
`match_ci` | Checks if the $data entry matches (case insensitive) the validation value (as strings) | The value to match against
`max_len` | Checks if the $data entry has a maximum length of the validation value (as integers) | the number representing the maximum length
`min_len` | Checks if the $data entry has a minimum length of the validation value (as integers) | the number representing the minimum length
`range` | Checks if the $data entry is between the lower and upper bounds of the validation values, including the bounds (as floats) | the lower bound followed by the upper bound, separated by `;`
`regex` | Checks if the $data entry matches the pattern given as validation value. | the pattern (ex: /^[a-z]+$/)
`required` | Checks if the $data entry exists and is not an empty string | none

<br>
#### => Example:  

Given the inputs:  

```
$data = [
	"user_name" => "Pedro Henriques",
	"email" => "pedro@pedrojhenriques.com",
	"password" => "pw123",
	"password_conf" => "pw456",
	"gender" => "male",
	"commission" => "200",
	"planet" => "Mars"
];
```

and the validations:  

```
$validations = [
	"user_name;User Name" => ["required", "max_len;50"],
	"email;Email" => ["required", "email"],
	"password;Password" => ["required", "min_len;6", "max_len;50"],
	"password_conf;Password Confirmation" => ["match;%password%"],
	"gender;Gender" => ["in;male;female"],
	"commission;Commission" => ["required", "range;0;100"],
	"planet;Planet of Origin" => ["required", "match;Earth"],
];
```

the following errors will be generated:  

```
Array
(
    [password] => Array
        (
            [0] => The length of the Password provided is too short.
        )

    [password_conf] => Array
        (
            [0] => The Password Confirmation provided doesn't match Password.
        )

    [commission] => Array
        (
            [0] => The value provided for the Commission field must be between 0 and 100.
        )

    [planet] => Array
        (
            [0] => The Planet of Origin provided doesn't match Earth.
        )

)
```

<br>
#### => Available Methods:  

Assuming `$validator` contains an instance of the DataValidator class.  

The following methods allow for the execution of the validations and access to their results:  

<br>
=> **Basic Methods**

Method | Description | Parameters | Use
--- | --- | --- | ---
validate | Executes all the validations and creates any error messages | array with the values to validate<br><br>array with the validation configuration<br><br>boolean controlling whether 1 failed check stops the rest from being executed, for each field | `$validator->validate($data, $validations[, $single_fail]);`<br><br>For further details, consult the topic **Using the Data Validator** above
passed | Returns `True` if all validations for all fields passed and `False` if at least 1 validation failed. | none | `$validator->passed();`
failed | Returns `True` if at least 1 validation failed and `False` if all validations for all fields passed. | none | `$validator->failed();`
getErrors | Returns the requested error(s) message(s). | string with the field name<br><br>integer with the error index | `$validator->getErrors([string $field, integer $index]);`<br><br>If just field is provided, then all errors for that field will be returned.<br>If no parameters are provided, then all errors for all fields will be returned.

<br>
=> **Advanced Methods**  

**NOTE:** These methods are used to customize the Data Validator. For further detail consult the topic **Customizing the Data Validator** below.

Method | Description | Parameters | Use
--- | --- | --- | ---
addValidation | Registers a temporary validation | string with the check type<br><br>function with the validation code | `$validator->addValidation(string $check_type, callable $function)`
addShortCircuit | Registers a new short-circuit rule | string with the check<br><br>array with the relevant $input values<br><br>boolean to flag if the check should be executed | `$validator->addShortCircuit(string $check, array $input_values, boolean $run_check)`
addMessage | Registers an error message | string with the message<br><br>string with the check type for this message<br><br>string with the field name for this message| `$validator->addMessage(string $message, string $check_type[, string $field])`<br><br>If $field is not provided, the error message will be applied to the provided check type for all fields.
getDebugErrors | Returns an array with any debug messages generated after calling `validate()` | none | `$validator->getDebugErrors();`<br><br>These include any validation classes not found, invalid syntax for the `$validations` array, among others.

<br>
## Customizing the Data Validator

It's possible to customize the validations as well as the error messages in 2 ways:  

1. **Persistent**: Adding a new validation as a `class` and an error message as an entry in the `error_msg.json` file.
2. **Temporary**: Registering a new validation as a temporary `function` and an error message as a temporary `string`.

<br>
### Validations:

When a validation is called, the code will first look for a temporary function with the same check type. If no matching function can be found the code will look for a defined class with a matching name (see below for naming convention).  
This means that custom temporary validations will have priority over the persistent validations. If you wish to change the behavior of a persistent validation, register a temporary function with the same check type.  

<br>
- **Classes:**  
	In regards to persistent validations, in the form of classes, the code expects the class to be named according to the following naming convention: `ValCheckType`.  
	All class names start with `Val`, followed by the check type in camel case style.  
	In check type, an underscore will be treated as the word separator.  

	These classes should implement the interface `iValidation`, located at `src/interfaces/interfaces.php`.  

	**NOTE:** The code expects a validation class to belong in the **DataValidator\Validations** namespace.

	As an example:  
	```
	namespace DataValidator\Validations;

	use DataValidator\Interfaces\iValidation;

	class ValMyClass implements iValidation {
		public function runCheck($field, $input, array $params) {
			// validation code goes here!
			// return True if the validation passed or False if it failed.
		}
	}
	```  

	The code will automatically look for, and include, a file with the same name as the validation class in the directory `src/validations`.  
	However, as long as the classes are defined by the time the code calls them, they don't need to be in a file with the same name, or in the default directory.  

	**EX:**  
	For a check type called `min_len` the code expects a class named `ValMinLen` and will automatically look for and include a file named `ValMinLen.php`.  
	For a check type called `required` the code expects a class named `ValRequired` and will look for a file named `ValRequired.php`.  

<br>
- **Functions:**  
	In regards to temporary validations, in the form of functions, they must be registered with the Data Validator before becoming available.  
	In order to register a validation function use the method `addValidation`, listed above as an advanced method.  

	The `$check_type` parameter must exactly match the check type provided in the `$validations` array of the `DataValidator` class' constructor.  

	The `$function` parameter must be a function that returns `True` if the check passed or `False` if it failed.  
	The function will be given the same parameters as the `runCheck()` method of the `iValidation` interface, located at `src/interfaces/interfaces.php`, so in order to have access to that information inside this function those parameters must be defined in the function's declaration.  
	The function can be lambda/anonymous or "named".  

	**EX:**  
	The following code will register a new temporary validation, accessible as check type `custom_val`:  
	```
	$validator->addValidation("custom_val", function($field, $input, array $params) {
		// validation code goes here!
		// return True if the validation passed or False if it failed.
	});
	```

<br>
- **Information available for the validation:**  
	When a validation is executed, either by calling the `runCheck` method of the validation class or by running the temporary `$function`, the following parameters are given to them:  

	- `$field`: String with the name of the $data entry being validated.<br><br>
	- `$input`: String with the value of the $data entry being validated. If `$field` doesn't exist in `$data`, then `$input` will be `null`.<br><br>
	- `$params`: Array with all validation values provided in the `$validations` array (zero-based indexed).  

<br>
**Examples:**  

Given the following code:  
```
$data = [
	"commission" => "20",
	"tax" => "25"
];

$validations = [
	"commission;Commission Perc." => ["custom_val;10;100;47"],
	"tax;Tax Perc." => ["match;%commission%"]
];
```

The information available to the validation method/function is:  

- For `$data["commission"]` and check type `custom_val`:
	<br>
	- `$field`: `"commission"`<br><br>
	- `$input`: `"20"`<br><br>
	- `$params`: `[0 => "0", 1 => "100", 2 => "47"]`

<br>
- For `$data["tax"]` and check type `match`:
	<br>
	- `$field`: `"tax"`<br><br>
	- `$input`: `"25"`<br><br>
	- `$params`: `[0 => "20"]`  -- Here `%commission%` was replaced by the commission's input

<br>
### Error Messages:

When an error message is generated, the code will first look for a temporary string with the same check type and field. If no matching string can be found the code will look for the persistent message with a matching name.  
This means that custom temporary strings will have priority over the persistent messages. If you wish to change the content of a persistent message, register a temporary string with the same check type.  

<br>
- **"error_msg.json":**  
	In regards to persistent messages, they must be stored in the `error_msg.json`, located by default at `src/data/error_msg.json`.  
	The keys should exactly match the check types provided in the `$validations` and the values should be their respective error messages.

<br>
- **Temporary String:**  
	In regards to temporary messages, they must be registered with the Data Validator before becoming available.  
	In order to register a temporary message use the method `addMessage`, listed above as an advanced method.  

	The `$check_type` parameter must exactly match the check type provided in the `$validations` array of the `DataValidator` class' constructor.

<br>
- **Information available for dynamic error messages:**  
	The following special keywords can be used in the error message string allowing for dynamic messages:  

	- `%field%`: Will be replaced by the name of the field were the error occurred.  
		Will use the field's alias, if one was provided.<br><br>
	- `%check_type%`: Will be replaced by the check type were the error occurred.<br><br>
	- `%input%`: Will be replaced by the $data value being validated that generated the error.<br><br>
	- `%params[key]%`: Will be replaced by the actual validation value in use with the given "key"<br><br>
	- `%%key%%`: Will be replaced by the value in $validations with the given "key"  
		If the value was another field, it's alias will be used, if one was provided.<br><br>

	**NOTE:** If "key" is `...`, then all available values for the failed validation will be displayed, separated by `, `

<br>
**Examples:**  

Given the following code:  
```
$data = [
	"commission" => "20",
	"tax" => "25"
];

$validations = [
	"commission;Commission Perc." => ["custom_val;10;100;47"],
	"tax;Tax Perc." => ["match;%commission%"]
];
```

The information available to the error messages is:  

- For `$data["commission"]` and check type `custom_val`:
	<br>
	- `%field%`: `Commission Perc.`<br><br>
	- `%check_type%`: `custom_val`<br><br>
	- `%input%`: `20`<br><br>
	- `%params[0]%`: `10`<br><br>
	- `%params[1]%`: `100`<br><br>
	- `%params[2]%`: `47`<br><br>
	- `%params[...]%`: `10, 100, 47`<br><br>
	- `%%0%%`: `10`<br><br>
	- `%%1%%`: `100`<br><br>
	- `%%2%%`: `47`<br><br>
	- `%%...%%`: `10, 100, 47`

<br>
- For `$data["tax"]` and check type `match`:
	<br>
	- `%field%`: `tax`<br><br>
	- `%check_type%`: `match`<br><br>
	- `%input%`: `25`<br><br>
	- `%params[0]%`: `20`<br><br>
	- `%params[...]%`: `20`<br><br>
	- `%%0%%`: `Commission Perc.`<br><br>
	- `%%...%%`: `Commission Perc.`

<br>
### Short-Circuit Rules:

It's possible to configure certain combinations of check types and input values that will "short-circuit" the rest of the validations, preventing them from being executed.  

In order to register a temporary short-circuit rule use the method addShortCircuit, listed above as an advanced method.

The permanent short-circuit combinations are defined in the `$short_circuit_rules` variable, located at the start of the `DataValidator` class' definition.  

By default this variable has the following value:  
```
$short_circuit_rules = [
	["check" => "required", "input" => [null, ""], "run_check" => true],
	["check" => "!required", "input" => [null, ""], "run_check" => false]
];
```  

The first entry reads as follows: **"if the field being validated has the `required` check and the $data entry is either `null` or `""`, then execute the `required` check but no others."**  
This prevents multiple error messages from being generated and checks being executed unnecessarily since there is no input value to validate and one was required.  
Note that by still executing the `required` check, which will fail, there will still be an error message being generated for that check.  

The second entry reads as follows: **"if the field being validated doesn't have the `required` check and the $data entry is either `null` or `""`, then don't execute the `required` check or any other checks."**  
This prevents an optional field that was left empty from being validated and potentially generating error messages, when no validation is required.  
However, if the field was provided (`$input` will be a non empty string) then any relevant checks will be executed.  
Note that an `!` is used to denote the absence of a check.
