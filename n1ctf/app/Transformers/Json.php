<?php

namespace App\Transformers;

class Json
{
  public static function response($code = null, $message = null,$success=null)
  {
    return [
      'code'    => $code,
      'message' => $message,
      'success' => $success
    ];
  }
}