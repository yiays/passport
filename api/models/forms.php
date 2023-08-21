<?php
/*
	Advanced form builder and auto validator.
*/

class Form{
	public $title;
	public $action;
	public $method;
	public $fields = [];
	public $buttons = [];
	public $rules = [];
	public $submit = null;
	
	function __construct($title, $action='', $method='GET')
	{
		$this->title = $title;
		$this->action = $action;
		$this->method = $method;
	}
	
	function __toString()
	{
		// Remove passwords first, so they're not printed.
		foreach($this->fields as $field){
			if($field->type == 'password'){
				$field->clear();
			}
		}
		
		return "
		<h2>$this->title</h2>
		<form action=\"$this->action\" method=\"$this->method\">
			".implode('', $this->fields)."
			".implode('', $this->buttons)."
		</form>
		";
	}
	
	function validate($data){
		$results = [];
		
		//Validate each field in the form
		foreach($this->fields as $field){
			$match = false;
			foreach($data as $fieldname=>$value){
				if($field->name == $fieldname){
					$match = true;
					$results []= $field->validate($value);
					break;
				}
			}
			if(!$match) $results []= $field->validate(null);
		}

		//Validate the entire form against rules
		foreach($this->rules as $rule){
			$result = $rule($data);
			$results []= $result;
			if(!$result->passed) break;
		}
		
		//Combine results into one FormValidationResult
		$success = true;
		$messages = [];
		foreach($results as $result){
			$messages []= $result->message;
			if(!$result->passed){
				$success = false;
			}
		}
		
		return new FormValidationResult($success, implode('<br>', array_filter($messages)));
	}
	
	function submit($data){
		if(!is_null($this->submit))
			return call_user_func($this->submit, $data);
		else
			return "To be implemented...";
	}
}
class FormField{
	public $type;
	public $name;
	private $_name;
	public $id;
	private $_id;
	public $value;
	private $_value;
	public $required;
	private $_required;
	public $readonly;
	private $_readonly;
	public $placeholder;
	private $_placeholder;
	public $autofocus;
	private $_autofocus;
	public $minlength;
	private $_minlength;
	public $maxlength;
	private $_maxlength;
	public $rows;
	private $_rows;
	public $cols;
	private $_cols;
	public $extra;
	
	function __construct($type, $name, $id=null, $value="", $required=false, $readonly=false, $placeholder=null, $autofocus=false, $minlength=null, $maxlength=null, $rows=null, $cols=null, $extra='')
	{
		$this->type = $type;
		$this->name = $name;
		$this->_name = "name=\"$name\"";
		$this->id = $id;
		$this->_id = !is_null($id)?"id=\"$id\"":'';
		$this->value = $value;
		$this->_value = strlen($value)?"value=\"$value\"":'';
		$this->required = $required;
		$this->_required = $required?'required':'';
		$this->readonly = $readonly;
		$this->_readonly = $readonly?'readonly':'';
		$this->placeholder = $placeholder;
		$this->_placeholder = !is_null($placeholder)?"placeholder=\"$placeholder\"":'';
		$this->autofocus = $autofocus;
		$this->_autofocus = $autofocus?'autofocus':'';
		$this->minlength = $minlength;
		$this->_minlength = !is_null($minlength)?"minlength=$minlength":'';
		$this->maxlength = $maxlength;
		$this->_maxlength = !is_null($maxlength)?"maxlength=$maxlength":'';
		$this->rows = $rows;
		$this->_rows = !is_null($rows)?"rows=$rows":'';
		$this->cols = $cols;
		$this->_cols = !is_null($cols)?"cols=$cols":'';
		$this->extra = $extra;
	}
	
	function __toString()
	{
		if($this->type == 'textarea'){
			return "
			<label for=\"$this->id\">".ucfirst($this->name).":</label><br>
			<textarea $this->name $this->_id $this->_required $this->_readonly $this->_placeholder $this->_autofocus $this->_minlength $this->_maxlength $this->_rows $this->_cols $this->extra>$this->value</textarea>
			";
		}
		return "
		<label for=\"$this->id\">".ucfirst($this->name).":</label><br>
		<input type=\"$this->type\" $this->_name $this->_id $this->_value $this->_required $this->_readonly $this->_placeholder $this->_autofocus $this->_minlength $this->_maxlength $this->extra><br>
		";
	}
	
	function clear(){
		$this->value = '';
		$this->_value = '';
	}
	
	function validate($value){
		$this->value = $value;
		$this->_value = "value=\"$value\"";
		if(is_null($value)){
			if($this->required){
				$this->extra = 'style="background-color:#fcc;"';
				return new FormValidationResult(false, ucfirst($this->name)." is required.");
			}
			return new FormValidationResult(true); // No need to validate, it wasn't provided.
		}
		if($this->type=='email' && !filter_var($value, FILTER_VALIDATE_EMAIL)){
			$this->extra = 'style="background-color:#fcc;"';
			return new FormValidationResult(false, ucfirst($this->name)." must be a valid email address.");
		}
		if($this->type=='number' && !is_numeric($value)){
			$this->extra = 'style="background-color:#fcc;"';
			return new FormValidationResult(false, ucfirst($this->name)." can contain only numbers.");
		}
		if($this->type=='url' && !filter_var($value, FILTER_VALIDATE_URL)){
			$this->extra = 'style="background-color:#fcc;"';
			return new FormValidationResult(false, ucfirst($this->name)." must be a valid url.");
		}
		if(!is_null($this->maxlength) && strlen($value)>$this->maxlength){
			$this->extra = 'style="background-color:#fcc;"';
			return new FormValidationResult(false, ucfirst($this->name)." is too long. (".strlen($value)." / $this->maxlength characters)");
		}
		if(!is_null($this->minlength) && strlen($value)<$this->minlength){
			$this->extra = 'style="background-color:#fcc;"';
			return new FormValidationResult(false, ucfirst($this->name)." is too short. (".strlen($value)." / $this->minlength characters)");
		}
		return new FormValidationResult(true);
	}
}
class FormButton{
	public $type;
	public $label;
	public $extra;
	
	function __construct($type='button', $label=null, $extra='')
	{
		$this->type = $type;
		$this->label = $label;
		$this->extra = $extra;
	}
	
	function __toString()
	{
		if($this->type == 'submit'){
			return "
			<input type=\"submit\" value=\"$this->label\" $this->extra>
			";
		}
		return "
		<button type=\"$this->type\" $this->extra>$this->label</button>
		";
	}
}
class FormValidationResult{
	public $passed;
	public $message;
	
	function __construct($passed, $message=null)
	{
		$this->passed = $passed;
		$this->message = $message;
	}
	function __toString()
	{
		return $this->passed?'Passed':$this->message;
	}
}

?>