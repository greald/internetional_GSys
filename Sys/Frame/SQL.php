<?php
require_once __DIR__."/../dbconnect.php";

class SQL
{
  public function __construct(){
    // empty constructor to allow for class name named method (SQL)
    // see https://www.php.net/manual/en/language.oop5.decon.php "Old-style constructors"
  }
  
  static function SQL( $QL )
  {
  // @param: String valid mySQL statement
  // return: Array results | Int rows affected | Bool at failure
   try 
    {
      // only open database here
      //require_once __DIR__."/../dbconnect.php";
      require_once DATABASE;
      
      if( strtoupper(substr(trim($QL),0,6)) == "SELECT" )
      {
        return (new ModelPDO)->Q2Assoc($QL); 
      }
      else
      {
        return (new ModelPDO)->exec($QL);
      }
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    {
      // closing database connection too often may induce false DDOS alert 
    } 
  }
  
  static function Create( $tablename, $insertvaluesarr = [], $test = FALSE )
  { 
    // echo "\n<br/>\n<br/>".__FILE__.__LINE__. " createopdracht ontvangen ";
    try 
    { 
      $into = ""; $vals = "";
      foreach( $insertvaluesarr as $field=>$crop )
      {
        $into .= " `".$field."` , ";
        $vals .= " '".$crop."' , ";
      }
      $into = substr($into, 0, -2); 
      $vals = substr($vals, 0, -2);
      
      $QI = "INSERT INTO `".$tablename."`
      (".$into.") 
      VALUES 
      (".$vals.")";
      
      // echo "\n<br/>\n<br/>".__FILE__.__LINE__. " createopdracht klaar voor verwerking ";
      if($test){ return $QI; }
      return self::SQL( $QI ); //(new ModelPDO)->exec($QI);
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    { ; } 
  } 

  static function Retrieve( $tablename, $where = TRUE, $test = FALSE )
  { 
    //$DB = null; // only intentionally establish database connection here!
    try 
    {
      $QL = "SELECT * FROM `".$tablename."` WHERE ".$where;
      if($test){ echo "\n<br/>\n<br/>".basename(__FILE__, ".php").__LINE__." SQL \n<br/>"; var_dump( $QL );}
      return self::SQL( $QL ); //(new ModelPDO)->Q2Assoc($QL);
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    { } 
  } 

  static function Update( $tablename, $fieldvaluearr = [], $where = FALSE, $test=FALSE )
  { 
    try 
    { 
      $setstring = "";
      foreach($fieldvaluearr as $field=>$newvalue )
      {
        $setstring .= " `".$field."` = '".$newvalue."', ";
      }
      $setstring = substr($setstring, 0, -2); // remove last comma + space
      
      $QU = "UPDATE `".$tablename."` 
            SET ".$setstring." 
            WHERE ".$where;
      
      if($test){ return $QU; }
      return self::SQL( $QU ); // (new ModelPDO)->exec($QU);
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    { } 
  } 

  static function Delete( $tablename, $where = FALSE, $test=FALSE )
  { 
    try 
    { 
      $QD = "DELETE FROM `".$tablename."` WHERE ".$where;
      if($test){ return $QD; }
      return self::SQL( $QD ); //  (new ModelPDO)->exec($QD);
    } 
    catch (\Exception $ex) 
    { return __CLASS__." ".__METHOD__.": Een fatale systeemfout: ". $ex->getMessage( ); } 
    finally //close database connection 
    { } 
  }
}
