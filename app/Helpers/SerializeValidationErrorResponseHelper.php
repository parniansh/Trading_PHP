<?php

namespace App\Helpers;
class SerializeValidationErrorResponseHelper
{
    public string $result = "خطا :";
    public function __construct(object $response){
        foreach ($response->toArray() as $key => $value){
            $this->result = $this->result . "\r\n" . $value[0];
        }
        return true;
    }
}
