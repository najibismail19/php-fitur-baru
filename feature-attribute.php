<?php
// Attribute
#[Attribute]
class NotBlank {

}

// Attribut Validation Emails
#[Attribute(Attribute::TARGET_PROPERTY)]
class Emails {
    private array $emails;
    private string $categories;
    public function __construct(array $array) {
        foreach($array as $value) {
            if(trim($value) == "") {
                throw new Exception("Declaration email is not value");
            }
            $this->emails[] = $value;
        }
    }

    public function getEmails() {
        $str = "";
        foreach($this->emails as $email) {
            $str .= $email . "|"; 
        }
        $length = strlen($str) - 1;
        $str = substr($str, 0, $length);
        return $str;
    }
}

// Attribut Validation Length 
#[Attribute(Attribute::TARGET_PROPERTY)]
class Length {

    public int $min;
    public int $max;

    public function __construct(int $min, int $max){
        $this->min = $min;
        $this->max = $max;
    }
}

// Object Login Request in User 
class LoginRequest {

    #[NotBlank]
    #[Length(min : 6, max : 10)]
    public ?string $username;
     
    #[NotBlank]
    #[Length(min : 6, max : 10)]
    public ?string $password;

    #[Emails(["@gmail.com", "@yahoo.com", "@example.com"])]
    public ?string $email; 
}

// Funtion Validation Input
function validation(object $object) : void
{
    // Reflection
    $class = new ReflectionClass($object);
    // GetProperty Return Array of Object ReflectionProperty
    $properties = $class->getProperties();
    // Looping Onject ReflectionProperty
    foreach($properties as $property) {
        // Validation Initialied
        validationNotBlank($property, $object);
        // Validation Length
        validationLength($property, $object);
        // Validation Emails
        validationEmails($property, $object);
    }
}

// Function ValidationEmails
function validationEmails(ReflectionProperty $property, $object){
    if(!$property->isInitialized($object) || $property->getValue($object) == null){
        return;
    }
    $emails = $property->getAttributes("Emails", ReflectionAttribute::IS_INSTANCEOF);
    $value = $property->getValue($object);
    foreach ($emails as $email) {
        $objEmail = $email->newInstance();
        $result = (bool) preg_match_all("/" . $objEmail->getEmails() . "/i", $property->getValue($object));
        if(!$result) {
            throw new Exception("Email {$property->getValue($object)} is in Valid!");
        }
    }
}

// Function ValidationLength
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

// Function ValidationInitialized
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

// Test Validation 
$najib = new LoginRequest();
$najib->username = "Najib";
$najib->password = "Rahasia123";
$najib->email = "Najib@gmail.com";

// Coba validation
try {
    validation($najib);
// Tangkap Error Exception
} catch(Exception $e) {
    // Tampilkan Message/Pesan
    echo $e->getMessage();
}
