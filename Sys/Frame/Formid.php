<?php
require_once "Input.php";
require_once "Model.php";
require_once "Controller.php";
require_once "View.php";

if(! function_exists("randomString"))
{
  function randomString($size)
  {
    //$Rep = str_split("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
    // $Rep = array_merge( range('0','9'), range('a','z'), range('A','Z') );
    $Rep = specify::formidtoken()['range'];
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
// Set global constant for minimum time in seconds, 
// between sending a form and receiving an human answer
if (!defined("FASTESTHUMANREACTION")){define("FASTESTHUMANREACTION", 2);} // 

class Formidtable extends Model
{
  const TABLENAME = DBPREFIX."_formid";
  const VELDNAMEN = ['formid'=>'formid',
//  'flashssid'=>'flashssid',
    'pedigree'=>'pedigree','first'=>'first','latest'=>'latest'];
  const SCHERMNAMEN = self::VELDNAMEN;
  const CREATETABLE="CREATE TABLE IF NOT EXISTS `".self::TABLENAME."` 
  (
  `".self::TABLENAME."_Rec` INT(8) NOT NULL AUTO_INCREMENT ,
  `".self::VELDNAMEN['formid'].  "` VARCHAR(16) NOT NULL , ".
//"  `".self::VELDNAMEN['flashssid'].    "` VARCHAR(64) NOT NULL , ".
  "  `".self::VELDNAMEN['pedigree'].  "` VARCHAR(64) NOT NULL , 
  `".self::VELDNAMEN['first'].		"` VARCHAR(64) NOT NULL ,
  `".self::VELDNAMEN['latest'].		"` VARCHAR(64) NOT NULL ,

  PRIMARY KEY (`".self::TABLENAME."_Rec`), 
  UNIQUE `index".self::VELDNAMEN['formid'].	"` (`".self::VELDNAMEN['formid'].	"`), ".
//"  INDEX `index".self::VELDNAMEN['flashssid'].  "` (`".self::VELDNAMEN['flashssid']."`), ".
  "  INDEX `index".self::VELDNAMEN['pedigree'].    "` (`".self::VELDNAMEN['pedigree']."`),
  INDEX `index".self::VELDNAMEN['first'].	"` (`".self::VELDNAMEN['first']."`), 
  INDEX `index".self::VELDNAMEN['latest'].		"` (`".self::VELDNAMEN['latest']."`)
  )  ;";
  
  protected function __construct(){ self::SQL( self::CREATETABLE ); }
}

class Formid extends Formidtable
{
  const FASTESTHUMANREACTION = FASTESTHUMANREACTION;

  public $formid = "";
  protected $pedigree = "";
  protected $first = "";
  protected $latest = "";
  protected $foundrow = [];
  protected $norobot = FALSE;
  protected $generated = FALSE;
  protected $validated = FALSE;
  public $approved = FALSE;
  protected $instantiated = FALSE;
  
  // if define a constructor then call parent class constructor explicitly
  // https://www.php.net/manual/en/language.oop5.decon.php
  // protected constructors MUST be called explicitely in child
  public function __construct(){parent::__construct();}
  
  public function run()
  {
    // cleanup database first
    $this->Prune();
    
    $inputgetpost = Input::get_('post');
    
    // run the gauntlet
    if ( ! empty($inputgetpost[self::SCHERMNAMEN['formid']]) )
    { // incoming from request
//    echo "\n<br/>".__METHOD__.__LINE__." formid inputgetpost:\n<br/>";var_dump($inputgetpost[self::SCHERMNAMEN['formid']]);
      if (! $this->findrow( $inputgetpost[self::SCHERMNAMEN['formid']] )==[])
      {
        if ($this->instantiate())
        {
          if ($this->norobot())
          {
            if ($this->validate() )
            {
              $this->punchticket();
              if ( $this->approved)
              {
//              echo "\n<br/>".__METHOD__.__LINE__." Formid after passing running the gauntlet:\n<br/>";var_dump($this);
                return $this;
              }
            }
          }
          else
          { // preventing endless loop
            $this->punchticket(DOCID);
            // invalidating the tokens, but then
            $this->approved = FALSE;
            // echo "Suspicious behaviour detected\n<br/>"; 
            return NULL;
          } 
        }
      }
    }
    // in all other cases
    // echo "\n<br/>".__METHOD__.__LINE__." NEWFormid aanmaken\n<br/>";
    $this->GenerateUnique( );
    $this->findrow( $this->formid );
    // echo "\n<br/>".__METHOD__.__LINE__." Formid after failing running the gauntlet:\n<br/>";var_dump($this);
    return $this;
  }
  
  public function GenerateUnique(  )
  {
    $this->formid = $this->gencode();
    $this->pedigree = $_SERVER['REQUEST_URI'];
    $this->first = DOCSTARTTIME;
    $this->latest = DOCSTARTTIME;
    
    $insertvaluesarr = [
      self::VELDNAMEN['formid']    =>$this->formid,
      //        self::VELDNAMEN['flashssid'] =>$this->getFlashssid(),
      self::VELDNAMEN['pedigree']  =>$this->pedigree,
      self::VELDNAMEN['first']     =>$this->first,
      self::VELDNAMEN['latest']    =>$this->latest
    ];
      
//    $this->generated = $this->CreateFormid( $insertvaluesarr )  >0; //bool
    $this->generated = self::Create( self::TABLENAME, $insertvaluesarr ) >0; // , TRUE ) ; //
    
    // instantiate indicator properties
    $this->norobot      = TRUE;
    $this->validated    = TRUE;
    $this->approved     = FALSE;
    $this->instantiated = TRUE;
    
    return $this->generated; // bool
  }
  
  private function gencode()
  {
    $formidlen = (specify::formidtoken())['size'];
    //16; // 47 672 401 706 823 533 450 263 330 816 permutations: ghvernuft.nl/os/grotegetallen.html
    // 78k Avogadro, or # gas molecules in a 5 floor high cube; or pretty much like the y-2023 NL-9728EA-1 buiding
    // Business rules
    
    // assemble $mFormid from global DOCID and random part
    $restlen = $formidlen - strlen(DOCID);
    $proposal = DOCID . randomString($restlen);
    // catch a, most unlikely, double
    if ( $this->findrow( $proposal, FALSE ) != [])
    {
      // die("\n<br/>\n<br/>".__METHOD__.__LINE__." double found! For ".$this->getFormid()." to replace ".$value ?? "null indeed");
      // maintain this line to test stability
      $proposal = $this->gencode(); // recursive call!
    }
    return $proposal;
  }
  
  private function findrow( $suspect, $hold = TRUE )
  {
    // @param $suspect: string to find in TABLENAME
    // @param $hold: bool to replace $this->foundrow with found row in TABLENAME
    // return: new found row in TABLENAME || existing found row
    $where = "`".self::VELDNAMEN['formid']."` = '". $suspect ."'";
    $valarr = ( $this->foundrow[self::VELDNAMEN['formid']] ?? NULL ) == $suspect
    ? [$this->foundrow]
    : self::Retrieve( self::TABLENAME, $where );
    if ( !empty($valarr) && $hold ){$this->foundrow = $valarr[0];
    }
    return $valarr[0] ?? $this->foundrow;
  }
  
  protected function norobot()
  {
    // return bool:
    // FALSE : interpreted as Robot - 'too fast to be human'
    // TRUE  : assumed no robot
    //  if( $this->validated == [] )
    //  if( Input::get_('post')[self::SCHERMNAMEN['formid']] ?? "" == "" )
    if ( $this->generated )
    {
      //    echo "\n<br/>\n<br/>".__FILE__.__LINE__." no input no robot ";
      $this->norobot = TRUE;
    }
    else
    {
      $this->norobot = $this->latest < DOCSTARTTIME - self::FASTESTHUMANREACTION;
    }
    return $this->norobot;
  }
  
  protected function instantiate()
  {
  // instantiate $this with values from $this->foundrow
  // return $this->instantiated
    
//    $this->foundrow = $this->foundrow == [] ? $this->findrow(self::VELDNAMEN['formid']) : $this->foundrow ;
    
    // do nothing if foundrow is not filled
    if ( $this->foundrow == [] ){ return $this->instantiated; }
    
    $this->formid = $this->foundrow[self::VELDNAMEN['formid']];
    $this->pedigree = $this->foundrow[self::VELDNAMEN['pedigree']];
    $this->first = $this->foundrow[self::VELDNAMEN['first']];
    $this->latest = $this->foundrow[self::VELDNAMEN['latest']];
    
    $this->instantiated = TRUE;
    return $this->instantiated;
  }
  
  protected function validate()
  {
//  // dummy om de cyclus te testen
//  $this->validated = TRUE;
//  return $this->validated;
    
    $this->formid = Sanitizer::formidtoken($this->formid);    
    $this->validated = is_null($this->formid) ? FALSE : TRUE;
    return $this->validated;
  }
  
  public function punchTicket( $docid = NULL ) // or $docid = DOCID
  {
    // deleting formid on approval
    // @param $docid either global constant DOCID or anything else
    //        in case DOCID then all formid rows in document are deleted
    // return bool: whether valid formids have been deleted
    //        deletions >0 marking approval; else refusal
    ////if( $this->validated == [] )
    //  if( Input::get_('post')[self::SCHERMNAMEN['formid']] ?? "" == "" )
    if ( $this->generated )
    {
      //    echo "\n<br/>\n<br/>".__METHOD__.__LINE__." no ticket to punch ";
      $this->approved = FALSE;
    }
    else
    {
      if ( $docid == DOCID )
      {
        $where = " substring(`".self::VELDNAMEN['formid']."`, 1, ".strlen(DOCID)." ) = '".DOCID."' ";
      }
      else
      {
        $where = " `".self::VELDNAMEN['formid']."` = '".Input::get_('post')[self::SCHERMNAMEN['formid']]."'";
      }
      // successfull deletion marks an approval
      $this->approved = self::Delete( self::TABLENAME, $where ) > 0 ; // , TRUE ); //
    }
    return $this->approved;
  }

  protected function Prune( $formidlifetime = FORMIDLIFETIME )
  {
  $where = " `".self::VELDNAMEN['first']."` < UNIX_TIMESTAMP() - ".$formidlifetime;
  return self::Delete( self::TABLENAME, $where);//, TRUE );
  }
  
  private function dispose()
  {
    foreach ( get_class_vars(__CLASS__) as $var=>$val)
    {
      $this->$$var = $val;
    }
  }
  
  public function test()
  {
    echo "<!doctype html><html><head></head><body>";
    $F=$this; //new Formid;
    echo "\n<br/>".__METHOD__.__LINE__." after running: ";var_dump([
      $F->run()
    ]);
    echo "<hr/>". (Input::get_('post')==[]
    ? "<form id=\"F\" method=\"post\" >
            <input type=\"text\" name=\"formid\" value=\"".$F->formid."\" />
            <input type=\"submit\" value=\"try\" />
          </form>
          <script>
            // to test Formid::norobot(). Should result NULL
            // document.getElementById('F').submit();
          </script>
        </body></html>"
    : "check post");
    // die("\n<br>tot hier");
  }
}

class Formidcontroller extends Controller
{
  public function run( Formid $formidobj = NULL )
  {
  // selects appropriate view behavior for $formidobj
    
    $formidobj = empty( $formidobj ) ? new Formid : $formidobj;
    $formidinst = $formidobj->run();
    
    if ( empty($formidinst) )
    {
      $this->viewstring("Suspicious behaviour detected\n<br/>");
    }
    elseif ( $formidinst->approved )
    {
      var_dump($formidinst);
    }
    else
    {
//    (new Formidview)->testview( $formidinst->formid );
      (new Formidview)->screenline([ Formid::SCHERMNAMEN['formid'] => $formidinst->formid ]);
    }
  }
  
  public function formElement()
  {
    
  }
  
  public function test()
  {
    $formidobj = new Formid;
    $this->run( $formidobj );
  }
}

class Formidview extends View
{
  public function testview($formid)
  {
    ?>
    <form id="F" method="post" >
      <?php
      $this->screenline([ Formid::SCHERMNAMEN['formid']=>$formid ]);
      ?>      
      <input type="submit" value="try" />
      <script>
    //// to test Formid::norobot(). Should result NULL
    // document.getElementById('F').submit()
    </script>
    </form>
    <?php
  }
  
  public function screenline( array $formidpair = [])
  {
    foreach ( $formidpair as $formidkey=>$formidval )
    {
    ?>
      <input type="text" name="<?= $formidkey ?>" value="<?= $formidval ?>" />
    <?php
    }
  }

}