<?php

#[Attribute]
class NotBlank {

}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Emails {
    public array $emails;
    public function __construct(array $array) {
        foreach($array as $value) {
            if(trim($value) == "") {
                throw new Exception("Declaration email is not value");
            }
            $this->emails[] = $value;
        }
    }
}


#[Attribute(Attribute::TARGET_PROPERTY)]
class Length {

    public int $min;
    public int $max;

    public function __construct(int $min, int $max){
        $this->min = $min;
        $this->max = $max;
    }
}

class LoginRequest {

    #[NotBlank]
    #[Length(min : 6, max : 10)]
    public ?string $username;
     
    #[NotBlank]
    #[Length(min : 6, max : 10)]
    public ?string $password;

    #[Emails(["@gmail.com", "@yahoo.com"])]
    public ?string $email; 
}

function validation(object $object) : void
{
    $class = new ReflectionClass($object);
    $properties = $class->getProperties();
    foreach($properties as $property) {
        validationNotBlank($property, $object);
        validationLength($property, $object);
        validationEmails($property, $object);
    }
}

function validationEmails(ReflectionProperty $property, $object){
    if(!$property->isInitialized($object) || $property->getValue($object) == null){
        return;
    }
    $emails = $property->getAttributes("Emails", ReflectionAttribute::IS_INSTANCEOF);
    $value = $property->getValue($object);
    foreach ($emails as $email) {
        $fullEmails = $email->newInstance();
      
    }
}

function validationLength(ReflectionProperty $property, $object){
    if(!$property->isInitialized($object) || $property->getValue($object) == null){
        return;
    }
    $value = $property->getValue($object);
    $attributs = $property->getAttributes('Length', ReflectionAttribute::IS_INSTANCEOF);
    foreach ($attributs as $attribute) {
        if(count($attributs) > 0) {
            $length = $attribute->newInstance();
            if(strlen($value) < $length->min) {
                throw new Exception("$value is to short");
            } else if(strlen($value) > $length->max) {
                throw new Exception("$value is to max");
            }
        }
    }
}

function validationNotBlank(ReflectionProperty $property, $object)
{
    $attributs = $property->getAttributes(NotBlank::class);
        if(count($attributs) > 0){
            if(!$property->isInitialized($object)) {
                throw new Exception("is property {$property->getName()} is not Initialized");
            } else if($property->getValue($object) == null){
                throw new Exception("is property {$property->getName()} is not null");
            }
        }
}

$najib = new LoginRequest();
$najib->username = "sdasdsas";
$najib->password = "sadasada";
$najib->email = "sadas@gmail.com";

try {
    validation($najib);
} catch(Exception $e) {
    echo $e->getMessage();
}
