<?php
// security restrictions

require_once __DIR__."/Frame/Input.php";
Input::quarantine();

//if(defined('FORMIDREQUIRED') && FORMIDREQUIRED && $_POST!=[] )
if(defined('FORMIDREQUIRED') && FORMIDREQUIRED && Input::get_('post')!=[] )
{
//if(isset($_POST['formid']))
  if(isset(Input::get_('post')['formid']))
  {
    require_once SYSROOT."/Frame/Formid.php";
  }
  else
  {
    die( basename(__FILE__.".php").__LINE__."\nformid required\nwhy? Check ".APPROOT."/config.php ;\nhow? Check ".SYSROOT."/Frame/Formid.php" ); 
  } 
}

class ModelPDO extends PDO
{
  public function __construct()
  {
  // inspiratiebron: http://php.net/manual/en/class.pdo.php#89019
  // skipping .ini files alltogether - they can't be hidden for browser
    require_once APPROOT.DIRECTORY_SEPARATOR."dbini.php";
         
    $dns = 	DATABASE_DRIVER .
    ':host=' . DATABASE_HOST .
    ';dbname=' . DATABASE_SCHEMA .
    ';charset='. DATABASE_CHARSET;
    
		try 
		{
	    parent::__construct($dns, DATABASE_USERNAME, DATABASE_PASSWORD);
		}
    catch (PDOException $e) 
	 	{
	    //print "Error!: " . $e->getMessage() . "<br/>";
	    error_log($e->getMessage(), 0);
	    error_log($e->getMessage(), 1, "troubleshooter@citytransportservice.nl");
	    die("Database connection error.<br/>Try again later.<br/>Sorry</body></html>");
	 	}
  }
    
  public function Q2Assoc($Query)
	{
		if($Ruery = $this->query($Query)){;}else{return [];}
		$uery=[]; while($Suery = $Ruery->fetch(PDO::FETCH_ASSOC))
		{
			$uery[]=$Suery;
		}
		return $uery;
	}

  public function Q2All($Query)
	{
		if($Ruery = $this->query($Query)){;}else{return [];}
		$uery=[]; while($Suery = $Ruery->fetch())
		{
			$uery[]=$Suery;
		}
		return $uery;
	}

	public function test($Query)
	{
		return $Query;
	}
}

$correctdbconnect="database connected all right"; //controlevariabele
//require_once("../phpfuncties/numRows.fun.php"); //definitie numRows($AnyTypeOfVariable)

// voor geheim gedoe ======================================================================================
$tekenpopulatie=explode(",","a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,0,1,2,3,4,5,6,7,8,9");
// te gebruiken in:
function ghv_random_tekenreeks($lengte,$tekenset)
{
	$Rteken="";
	for($i=0; $i < $lengte; $i++)
	{
	$X=rand(0,sizeof($tekenset)-1);
	$Rteken.=$tekenset[$X];	
	}
	return $Rteken;
}
// en dan te vervormen met
function vervorm($string)
{
  return hash('sha256', $string);
}

?>