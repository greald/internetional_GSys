<?php
require_once "Controller.php";
require_once "Input.php";
require_once "Formid.php";

class Formelement
{
  const ELEMENTS = ["input", "label", "select", "textarea", "button", "fieldset", "legend", "datalist", "output", "option", "optgroup"];
  
  const INPUTTYPES = ["button","checkbox","color","date","datetime-local","email","file","hidden","image","month","number","password","radio","range","reset","search","submit","tel","text","time","url","week"];

  // on questionnaire
  public $schermnaam = "formid";
  public $elementTag ="input";
  public $attributes =["type"=>"hidden"];
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
    require_once SYSROOT.DIRECTORY_SEPARATOR."isifisset.php";
    $this->formmethod = isifisset( $this->formattributes["method"], $this->formmethod );
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
  public function addFormelement($namekey, Formelement $valobj)
  {
    if(!isset($this->formelements[Formid::SCHERMNAMEN['formid']]))
    {
      $formid = Formid::SCHERMNAMEN['formid'];
      $this->formelements[$formid] = new Formelement;
      $this->formelements[$formid]->attributes['value'] = "<"."?= "."(new Formid)->run()"." ?".">";
      
	  Input::whitelist( $this->formmethod, $formid );
	  Input::whitelist( "request", $formid );
    }
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
  
  public function indata( Formelement $formelement )
  {
    // authenticatation
    Input::quarantine();
    $formid = new Formid;
    $auth = $formid->run();
    
    // validation/sanitation
    if($auth)
    {
      
    }
    else
    {
      return "oops";
    }
    
    // release
  }
}

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