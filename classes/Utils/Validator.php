<?php
declare(strict_types=1);

namespace Consolety\Utils;

class Validator {
	private $errors=[];

	public function validate_keys(array $needed,$data){
		$this->errors=[];
		foreach ($needed as $var){
			if(empty($data->{$var}) && $data->{$var} !== 0){
				$this->errors[$var][] = 'This value should not be empty';
			}
		}
	}

	public function get_errors():array
	{
		return $this->errors;
	}
}