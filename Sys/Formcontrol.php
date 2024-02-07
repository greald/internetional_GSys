<?php
require_once "Controller.php";
require_once "Input.php";
require_once "Formid.php";

class Formelement
{
  const ELEMENTS = ["input", "label", "select", "textarea", "button", "fieldset", "legend", "datalist", "output", "option", "optgroup"];
  
  const INPUTTYPES = ["button","checkbox","color","date","datetime-local","email","file","hidden","image","month","number","password","radio","range","reset","search","submit","tel","text","time","url","week"];

  // on questionnaire, preset to formid
  public $schermnaam = "formid";
  public $elementTag ="input";
  public $attributes =["type"=>"hidden"]; // "text"]; //
  public $innerHTML ="";
  
  // on intake
  public $model = "Formid";
  
  public $validatorclass = "Formid";
  public $validatormethod = "validizer"; // from inputFilters.php included in Input.php
  public $sanitizerclass =  "Formid";
  public $sanitizermethod = "sanitizer";//"mbVar";
}

class Formcontroller extends Controller
{
  public $formtag = "<form>";
  public $formattributes = [];
  public $formmethod = "post";
  
  private function setProperties()
  {
    //require_once SYSROOT.DIRECTORY_SEPARATOR."isifisset.php";
    //$this->formmethod = isifisset( $this->formattributes["method"], $this->formmethod, "_POST" );    
    if ( isset( $this->formattributes["method"] ))
    {
      $this->formmethod = $this->formattributes["method"];
    }
    $this->formattributes["method"] = $this->formmethod;
  }
  
  public function makeFormtag()
  {
    $this->setProperties();
    
    $this->formtag = "<form";
    foreach($this->formattributes as $attribute=>$param)
    {
      $this->formtag .= " ".$attribute."=\"".$param."\"";
    }
    $this->formtag .= ">";
  }

  private $formelements = [];
  public function getFormElements(){ return $this->formelements; }
  public function addFormelement($namekey, Formelement $valobj)
  {
    // special treat for formid element
    $formidschermnaam = Formid::SCHERMNAMEN['formid'];
    if (!isset($this->formelements[$formidschermnaam]))
    {
      $token = $this->getFormid()->formid; //(new Formid)->run()->formid;
      
      $this->formelements[$formidschermnaam] = new Formelement;
      $this->formelements[$formidschermnaam]->attributes['value'] = $token;
      
      Input::whitelist( $this->formmethod, $formidschermnaam );
      Input::whitelist( "request", $formidschermnaam );
    }
    
    // separate information carrying form elements from the auxiliary ones
    if ( isset( $valobj->attributes['anon'] ) && $valobj->attributes['anon'] == 'ymous' )
    {
      $namekey = null;
      unset($valobj->attributes['anon']);
    }
    
    // either assign name-attribute to elements, or just number them
    if( strlen($namekey)>0 )
    {
      $this->formelements[$namekey] = $valobj;
      $schermnaam = $this->formelements[$namekey]->schermnaam;
      
      //Input::$whitelisted_keys[] = $schermnaam;
      
      Input::whitelist( $this->formmethod, $schermnaam );
  	  Input::whitelist( "request", $schermnaam );
  	}
    else
    {
      $this->formelements[] = $valobj;
      // unnamed Input entries are to be ignored in $this->makeForm()
      // Input::$whitelisted_keys[] = array_key_last($this->formelements);
    }
  }
  public function delFormelement($namekey, $valarr)
  {
    unset($this->formelements[$namekey]);
    //unset(Input::$whitelisted_keys[$namekey]);
  }

  private $formid = NULL;
  public function getFormid() // return Formid | Bool | null
  { 
    if(isset( $this->formid ) )
    { }
    else
    { 
      $this->formid = (new Formid)->run();
    }
    return $this->formid;
  }
  
  // only non-numeric indexes in $this->formelements are to be named inputs in the html form
  public function makeForm()
  {
    $this->makeFormtag();
    $form = $this->formtag .Indent::T();
    
    foreach($this->formelements as $nameindex=>$formelementInst)
    {
      if( in_array( $formelementInst->elementTag, Formelement::ELEMENTS ) )
      {
        $form .= Indent::nT(0)."<" . $formelementInst->elementTag .  
        // only non-numeric indexes in $this->formelements are to be named inputs in the html form 
        // split trunk from sequence index after "#", as set in self::generateFormelements() under function nrnamekey()
        ( is_numeric($nameindex) ? "" : " name=\"".(explode("#",$nameindex))[0]."\"" );
        foreach( $formelementInst->attributes as $att=>$param )
        {
          $form .= " ".$att."=\"".$param."\"";
        }
        if( $formelementInst->elementTag == "select" )
        {
          $optionsArr = explode(",", $formelementInst->innerHTML);
          $options = Indent::T(1);
          foreach($optionsArr as $optionVal){$options .= Indent::nT(0)."<option value=\"".$optionVal."\">".$optionVal."</option>"; }
          $formelementInst->innerHTML = $options;
          
          $form .= ">" . $formelementInst->innerHTML .Indent::nT(-1). "</" . $formelementInst->elementTag . ">" ;
        }
        else
        {
          $form .= 
            in_array($formelementInst->elementTag, ["input"]) 
            ? "/>". $formelementInst->innerHTML
            : ">" . $formelementInst->innerHTML . "</" . $formelementInst->elementTag . ">" ;
        }
     }
    }
    return $form.Indent::nT(-1)."</form>";
  }
  
  public function indata()// Formelement $formelement )
  {
    // authenticatation
    Input::quarantine();
    $getpost = Input::get_('post');
    
    if( $this->getFormid()->approved )
    {
//    echo "\n<br/>".__METHOD__.__LINE__." approved in indata: " ;var_dump( $getpost );
      return $getpost;
    }
    else
    {
      return "oops";
    }
    
    // release
  }
  
  public function run()
  {
  // return: 
  // either string - i.c quest-form
  // or array - i.c incoming $_POST-data
  // else bool false
    Input::quarantine();
    $getpost = Input::get_('post');
    
    if ( $getpost == [] ) // prepare outgoing request string
    {
//    echo "\n<br/>".__METHOD__.__LINE__." makeform\n<br/>(select all, and view page source, to check formid)\n<br/>";var_dump($this->formelements);
      return $this->makeForm();
    }
    else // process incoming reply
    {
//    echo "\n<br/>".__METHOD__.__LINE__." indata\n<br/>";
//    $indata=$this->indata() ;echo "\n<br/>".__METHOD__.__LINE__." indata\n<br/>";var_dump($indata);return $indata;
      return $this->indata();
    }
  }
}

class Formview extends View
{}

/*//  READ_ME ///////////////////////////////////////////////////////////
//    (1)
      echo Formcontroller::generateFormelements();
//    (2)
      $testform = new Formcontroller;
//    (3)
//    copy / paste result into your script
//    (4)
      echo $testform->makeForm();
*/