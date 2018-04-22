<?php
/**
 * Created by PhpStorm.
 * User: mcones
 * Date: 1/18/18
 * Time: 8:17 PM
 */

namespace Mcones\LaravelBaseHelpers\Exceptions;


class BaseException extends \Exception
{

    public $statusCode=400;
    public $key='';
    public $message='';
    public $additional_data=null;

    public function __construct($key='',$message='',$statusCode=400,$additional_data=null)
    {
        $this->key=$key;
        $this->message=$message;
        $this->statusCode=$statusCode;
        $this->additional_data=$additional_data;
    }


}