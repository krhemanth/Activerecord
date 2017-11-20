<?php
//turn on debugging messages
ini_set('display_errors', 'On');
error_reporting(E_ALL);
define('DATABASE', 'hk378');
define('USERNAME', 'hk378');
define('PASSWORD', 'friends4ever');
define('CONNECTION', 'sql1.njit.edu');
class dbConn{
    //variable to hold connection object.
    protected static $db;
    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        } 
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //return connection.
        return self::$db;
    }
}
class collection {
    static public function create() {
      $model = new static::$modelName;
      return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    
    static public function buildHtml($records,$obj)
    {
    $html="<table border='1'><tr>";
   foreach($obj as $key => $value)
   {
    $html.="<th>$key</th>";
   }
    $html.="</tr>";
    foreach($records as $row)
    {
    $rowHtml="<tr>";
    foreach($obj as $key => $value)
    {
    $rowHtml.="<td>";
    $rowHtml.=$row->$key;
    $rowHtml.="</td>";
    }
    $rowHtml.="</tr>";
    $html.=$rowHtml;
    }
    $html.="</table>";
    return $html;
    }

    
}
class accounts extends collection {
    protected static $modelName = 'account';
}
class todos extends collection {
    protected static $modelName = 'todo';
}
class model {
    protected $tableName;
    public function save()
    {
        if ($this->id = '') {
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }
        echo 'I just saved record: ' . $this->id;
    }
    public function insert() {
        $tableName = $this->tableName;
        $array = get_object_vars($this);
        $columns=array();$values=array();
         foreach($array as $key => $value)
        {
        if($key!="tableName")
          { array_push($columns,$key);
           array_push($values,$value);
           }
        }
        $columnString=implode(",",$columns);
        $valueString=implode("','",$values);
        $sql="INSERT INTO $tableName (".$columnString.") VALUES ('".$valueString."')";
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        echo $sql;
        echo "<br>Inserted Successfully";
        return $sql;
    }
    public function update() {
        $tableName = $this->tableName;
        $array = get_object_vars($this);
        $update=array();
         foreach($array as $key => $value)
        {
        if($key!="tableName"&&$key!="id")
          { 
          if($value!="")
          {
          array_push($update," $key='$value'");
          
          }
           }
        }
        $columnString=implode(",",$update);
        $sql="update $tableName set " .$columnString. " where id=$this->id";
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        echo $sql;
        echo "<br>I just updated record" . $this->id;
        return $sql;
    }
    public function delete() {
        $tableName = $this->tableName;
        $sql="delete from $tableName where id='$this->id'";
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        echo $sql;
        echo '<br>I just deleted record' . $this->id;
        return $sql;
        
    }
}
class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    public function __construct()
    { 
        $this->tableName = 'accounts';
    }
}

class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public function __construct()
    {
        $this->tableName = 'todos';
	
    }
}
// this would be the method to put in the index page for accounts





//this code is used to get one record and is used for showing one record or updating one record
$record = todos::findOne(1);
$result=todos::buildHtml($record,get_object_vars(todos::create()));
print_r($result);

$record = accounts::findOne(4);
$result=accounts::buildHtml($record,get_object_vars(accounts::create()));
print_r($result);


//this is used to save the record or update it (if you know how to make update work and insert)
// $record->save();
//$record = accounts::findOne(1);
//This is how you would save a new todo item

$record = new account();
$record->email="abc@njit.edu";
$record->fname="x";
$record->lname="y";
$record->phone="123455678";
$record->birthday="23-09-1994";
$record->gender="male";
$record->password="123";
$record->insert();
//print_r($record);
$record = accounts::create();
//print_r($record);

$records = accounts::findAll();
$result=accounts::buildHtml($records,get_object_vars(accounts::create()));
print_r($result);

$record = new account();
$record->id='11';
$record->email='rocks@njit.edu';
$record->fname="rash";
$record->lname="mica";
$record->phone="0790716794";
$record->birthday="31-08-1995";
$record->gender="female";
$record->password="12345";
$record->update();

$records = accounts::findAll();
$result=accounts::buildHtml($records,get_object_vars(accounts::create()));
print_r($result);

$record = new account();
$record->id='100';
$record->delete();

$records = accounts::findAll();
$result=accounts::buildHtml($records,get_object_vars(accounts::create()));
print_r($result);



$record = new todo();
$record->owneremail="krishna@njit.edu";
$record->ownerid="1";
$record->createddate="2018-10-23";
$record->duedate="2018-10-19";
$record->message="Hi All";
$record->isdone="0";
$record->insert();
//print_r($record);
$record = todos::create();
//print_r($record);

$records = todos::findAll();
$result=todos::buildHtml($records,get_object_vars(todos::create()));
print_r($result);

$record = new todo();
$record->id='23';
$record->owneremail="sample@njit.edu";
$record->ownerid="1";
$record->createddate="2018-10-23";
$record->duedate="2018-10-23";
$record->message="Get Later";
$record->isdone="1";
$record->update();

$records = todos::findAll();
$result=todos::buildHtml($records,get_object_vars(todos::create()));
print_r($result);

$record = new todo();
$record->id='25';
$record->delete();

$records = todos::findAll();
$result=todos::buildHtml($records,get_object_vars(todos::create()));
print_r($result);
