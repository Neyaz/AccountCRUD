<?php

namespace Core;

class ValidateException extends \Exception
{

    private $messages;
    /**
    * Конструктор
    *
    * @param array $message
    * @return ValidateException
    */
    function __construct($message = array()) {
        if(!is_array($message))
            $message = array($message);

        $this->messages = $message;
        parent::__construct(implode(", ",$message));
    }

    function GetErrors(){
        return $this->messages;
    }
    function GetErrorsString(){
        return implode('<br/>', $this->messages);
    }

}

class FileNotFoundException extends \Exception {};
class FileNotSavedException extends \Exception {};
class DirectoryNotFoundException extends FileNotFoundException {};
class LiteWorkInitializationException extends \Exception {}
class NullReferenceException extends \Exception {}
class UnexpectedExtensionException extends \Exception {}
class PermissionDeniedException extends \Exception {}
class NameNotAssignedException extends \Exception {}
class FileUploadErrorException extends \Exception {}
