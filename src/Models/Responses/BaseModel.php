<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class BaseModel
{
    public function __construct(?array $abstract)
    {
        foreach ($abstract as $key => $arg) {

            if (property_exists($this, $key)) {

				if (method_exists($this, "format_{$key}")){

					$func = "format_{$key}";

					self::$func($arg);

				}

                $this->$key = $arg;

            }

        }
    }

	protected function format_isActive(&$value){

		$value = (boolean)$value;

	}
}
