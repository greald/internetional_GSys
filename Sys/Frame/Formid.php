<?php
require_once "Model.php";

if(! function_exists("randomString"))
{
  function randomString($size)
  {
    //$Rep = str_split("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
    $Rep = array_merge( range('0','9'), range('a','z'), range('A','Z') );
    $Rndstr = "";
    for($i=0; $i<$size; $i++)
    {
      $Rndstr .= $Rep[rand(0, count($Rep)-1)];
    }
    return $Rndstr;
  }
}

if (!defined("DOCSTARTTIME")){define("DOCSTARTTIME",time());} // timestamp
// Generate (global) constant DOCID as identifier for all the request
if (!defined("DOCID")){define("DOCID",randomString(8));} // 218 340 105 584 896 options
// Set global constant for formid lifetime
if (!defined("FORMIDLIFETIME")){define("FORMIDLIFETIME", 1959);} // 
// Set global constant for minimum tim in seconds, 
// between sending a form and receiving an human answer
if (!defined("FASTESTHUMANREACTION")){define("FASTESTHUMANREACTION", 2);} // 

class Formidtable extends Model
{
  const TABLENAME = DBPREFIX."_formid";
  const VELDNAMEN = ['formid'=>'formid','flashssid'=>'flashssid','pedigree'=>'pedigree','first'=>'first','latest'=>'latest'];
  const SCHERMNAMEN = self::VELDNAMEN;
  const CREATETABLE="CREATE TABLE IF NOT EXISTS `".self::TABLENAME."` 
  (
  `".self::TABLENAME."_Rec` INT(8) NOT NULL AUTO_INCREMENT ,
  `".self::VELDNAMEN['formid'].	"` VARCHAR(16) NOT NULL , 
  `".self::VELDNAMEN['flashssid'].		"` VARCHAR(64) NOT NULL ,
  `".self::VELDNAMEN['pedigree'].	"` VARCHAR(64) NOT NULL , 
  `".self::VELDNAMEN['first'].		"` VARCHAR(64) NOT NULL ,
  `".self::VELDNAMEN['latest'].		"` VARCHAR(64) NOT NULL ,

  PRIMARY KEY (`".self::TABLENAME."_Rec`), 
  UNIQUE `index".self::VELDNAMEN['formid'].	"` (`".self::VELDNAMEN['formid'].	"`), 
  INDEX `index".self::VELDNAMEN['flashssid'].	"` (`".self::VELDNAMEN['flashssid']."`), 
  INDEX `index".self::VELDNAMEN['pedigree'].		"` (`".self::VELDNAMEN['pedigree']."`),
  INDEX `index".self::VELDNAMEN['first'].	"` (`".self::VELDNAMEN['first']."`), 
  INDEX `index".self::VELDNAMEN['latest'].		"` (`".self::VELDNAMEN['latest']."`)
  )  ;";
}

class Formid extends Formidtable
{
  const FASTESTHUMANREACTION = FASTESTHUMANREACTION; 
  
  // 'dataMember Formid'
  public $mFormid = "NULL";
  // Formid-property accessor / mutator alias 'getter and setter'
  public function getFormid(){return $this->mFormid;}
  public function setFormid($value = NULL)
  {
    $formidlen = 16; // 47 672 401 706 823 533 450 263 330 816 permutations: ghvernuft.nl/os/grotegetallen.html
    // Business rules
    if (NULL == $value)
    {
      // $this->mFormid = randomString($formidlen);
      
      // assemble $mFormid from global DOCID and random part
      $docidlen = $formidlen - strlen(DOCID);
      $this->mFormid = DOCID . randomString($docidlen);
      if( self::RetrieveFormid( " `formid`='".$this->getFormid()."' " ) != [])
      {
        // catch a, most unlikely, double
        $this->setFormid();
      }
    }
    elseif( strlen($value) == $formidlen)
    { $this->mFormid = $value; }
    else { throw new clsExceptionInvalidFormid( $value." invalid Formid" ); }
    }

  // 'dataMember Pedigree'
  // REQUEST_URI
  public $mPedigree = "NULL";
  // Pedigree-property accessor / mutator alias 'getter and setter'
  public function getPedigree(){return $this->mPedigree;}
  public function setPedigree($value = NULL)
  {
    // Business rule
    if (TRUE) // your business rule's condition here
    {
      $this->mPedigree = NULL == $value 
      ? $_SERVER['REQUEST_URI'] 
      : $value;
    }
    else { throw new clsExceptionInvalidPedigree( $value." invalid Pedigree" ); }
  }

  // 'dataMember First'
  public $mFirst = "";
  // First-property accessor / mutator alias 'getter and setter'
  public function getFirst(){return $this->mFirst;}
  public function setFirst($value = NULL)
  {
    // Business rule
    if (true) // your business rule's condition here
    {
      $this->mFirst = NULL == $value 
      ? DOCSTARTTIME : // time() : 
      $value; 
    }
    else { throw new clsExceptionInvalidFirst( $value." invalid First" ); }
  }

  // 'dataMember Latest'
  // time this record was checked latest
  public $mLatest = "";
  // Latest-property accessor / mutator alias 'getter and setter'
  public function getLatest(){return $this->mLatest;}
  public function setLatest($value = NULL)
  {
    // Business rule
    if (true) // your business rule's condition here
    {
      $this->mLatest = NULL == $value 
      ? DOCSTARTTIME : // time() : 
      $value;  
    }
    else { throw new clsExceptionInvalidLatest( $value." invalid Latest" ); }
  }
  
  public function run()
  {
    $this->prune();
    if( isset( Input::get_('post')[self::SCHERMNAMEN['formid']] ))
    {
//      echo "\n<br/>\n<br/>".__METHOD__.__LINE__. 
      $this->validate();
      if( $this->noRobot() )
      {
//        echo "\n<br/>\n<br/>".__METHOD__.__LINE__."formid approved";
        $reaction = $this->punchTicket(); // RETURN : bool
      }
      else
      {
//        echo "\n<br/>\n<br/>".__METHOD__.__LINE__."ignore robot";
        $this->punchTicket(); // deleting formid record
        $reaction = $this->GenerateUnique(  ); // RETURN : array
      }
    }
    else
    {
//      echo "\n<br/>\n<br/>".__METHOD__.__LINE__."release new formid";
      $reaction = $this->GenerateUnique(  ); // RETURN : array
    }
    return $reaction;
  }
  
  // Constructor
  public function __construct( )
  {
    $DB = new ModelPDO;
    $DB->exec(self::CREATETABLE);
  }
  
  // convenience crud methods
  
  static function CreateFormid( $insertvaluesarr = [], $test=FALSE )
  {
    return Model::Create( self::TABLENAME, $insertvaluesarr , $test );
  }
  
  static function RetrieveFormid( $where = TRUE )
  { 
    return Model::Retrieve( self::TABLENAME, $where);
  }
  
  static function UpdateFormid( $fieldvaluearr = [], $where = FALSE, $test=FALSE  )
  { 
    return Model::Update( self::TABLENAME, $fieldvaluearr, $where, $test );
  }
  
  static function DeleteFormid( $where = FALSE, $test=FALSE )
  { 
    return Model::Delete( self::TABLENAME, $where, $test );
  }
  
  // specific methods

  public function GenerateUnique(  )
  { 
    $DB = null; // only intentionally establish database connection here!
    try 
    {
      $this->setFormid();
//      $this->setFlashssid();
      $this->setPedigree();
      $this->setFirst();
      $this->setLatest();
      
      $insertvaluesarr = [
        self::VELDNAMEN['formid']    =>$this->getFormid(),
//        self::VELDNAMEN['flashssid'] =>$this->getFlashssid(),
        self::VELDNAMEN['pedigree']  =>$this->getPedigree(),
        self::VELDNAMEN['first']     =>$this->getFirst(),
        self::VELDNAMEN['latest']    =>$this->getLatest()
      ];
      
//      $this->nextCSRFtoken();
//      $insertvaluesarr[self::VELDNAMEN['flashssid']] =  $this->getFlashssid( );
      
      $created = $this->CreateFormid( $insertvaluesarr );
//    return $created;
      return $created >0 ? $this->screen() : ["no formid"] ;
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    { $DB = null; } 
  }
  
  public function screen()
  {
    return [
      "name"  => self::SCHERMNAMEN['formid'], 
      "id"    => self::SCHERMNAMEN['formid'], 
      "value" => $this->getFormid()
    ];
  }
  
  public $validated = [];
  public function validate( )
  {
  // Checks whether Input::get_('post')['formid'] is registered
  // sets $this->validated with valid formid row, or []
    try 
    {
      $where = "`".self::VELDNAMEN['formid']."` = '". Input::get_('post')[self::SCHERMNAMEN['formid']] ."'";
      $this->validated = Model::Retrieve( self::TABLENAME, $where )[0];
      return $this->validated;
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally 
    {  } 
  }
  public function validizer()
  {
	if ($this->validated == []) {$this->validate();}
    return $this->validated == [] ? FALSE : TRUE;
  }
  public function sanitizer()
  {
	  $this->validizer();
	  return $this->validated;
  }
  
  public function punchTicket( $docid = NULL ) // or $docid = DOCID
  {
  // deleting formid on approval
  // @param $docid either global constant DOCID or anything else
  //        in case DOCID then all formid rows in document are deleted
  // return bool: whether valid formids have been deleted 
  //        deletions >0 marking approval; else refusal
    if( $this->validated == [] )
    {
      return FALSE;
    }
    else
    {
      if( $docid == DOCID )
      {
        $where = " substring(`".self::VELDNAMEN['formid']."`, 1, ".strlen(DOCID)." ) = '".DOCID."' ";
      }
      else
      {
        $where = " `".self::VELDNAMEN['formid']."` = '".Input::get_('post')[self::SCHERMNAMEN['formid']]."'";        
      }
      // successfull deletion marks an approval
      return Model::Delete( self::TABLENAME, $where ) > 0 ; // , TRUE ); //
    }
  }
  
  public function noRobot( )
  {
  // return bool: 
  // FALSE : interpreted as Robot 'too fast to be human'
  // TRUE  : assumed noRobot
    if( $this->validated == [] )
    {
      // echo "\n<br/>\n<br/>".__FILE__.__LINE__." validator zou leeg zijn ";
      return FALSE;
    }
    else
    {
    //echo "\n<br/>".__METHOD__.__LINE__." : ". $this->validated[ self::VELDNAMEN['latest'] ]." < ". DOCSTARTTIME ." - ". self::FASTESTHUMANREACTION;
      return $this->validated[ self::VELDNAMEN['latest'] ] < DOCSTARTTIME - self::FASTESTHUMANREACTION;
    }
  }
  
  public function Prune( $formidlifetime = FORMIDLIFETIME )
  { 
    try 
    {
      $where = " `".self::VELDNAMEN['first']."` < UNIX_TIMESTAMP() - ".$formidlifetime;
      return Model::Delete( self::TABLENAME, $where);//, TRUE );
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally
    { } 
  } 

  public function dispose(){/* your dispose function code here */}

}

if(!class_exists('clsExceptionInvalidFormid'))
	{class clsExceptionInvalidFormid extends \Exception {};}
if(!class_exists('clsExceptionInvalidRequestIP'))
	{class clsExceptionInvalidRequestIP extends \Exception {};}
if(!class_exists('clsExceptionInvalidFlashssid'))
	{class clsExceptionInvalidFlashssid extends \Exception {};}
if(!class_exists('clsExceptionInvalidPedigree'))
	{class clsExceptionInvalidPedigree extends \Exception {};}
if(!class_exists('clsExceptionInvalidFirst'))
	{class clsExceptionInvalidFirst extends \Exception {};}
if(!class_exists('clsExceptionInvalidLatest'))
	{class clsExceptionInvalidLatest extends \Exception {};}
  
  
  
  