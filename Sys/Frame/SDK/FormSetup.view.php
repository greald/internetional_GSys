<?php
require_once "FormSetup.php";

function a2s($a)
{
  $u="[";
  foreach( $a as $i )
  {$u .= '"'.$i.'", ';}
  return substr($u, 0, -2) . ']';
}    
$elementsTagsList = a2s(Formelement::ELEMENTS);   
$inputTypesList   = a2s(Formelement::INPUTTYPES);

?>
<!DOCTYPE html>
<html><body>
  <script>
    const elementsTagsList = <?=  $elementsTagsList  ?>;
    const inputTypes       = <?=  $inputTypesList  ?>;
    
    function add()
    {
      document.getElementById('generatedFormelements').value += ''+
      '\$NElm = new Formelement; '+ // '"'+  +'"'+
      '\n\$NElm->elementTag = "'+ document.getElementById('elementTag').value +'";';
      
      let att = document.getElementById('attributes').value;
      console.log('125 attributes ' + att);
      let cleanatt = att.replace(/#\d*/g,'').replace(/=/g,'"=>"').replace(/,/g,'", "');
      console.log('127 cleanattributes ' + cleanatt);
      
      document.getElementById('generatedFormelements').value += ''+
      '\n\$NElm->attributes = ["'+ cleanatt +'"];';
      
      document.getElementById('generatedFormelements').value += ''+
      '\n\$NElm->innerHTML = "'+ document.getElementById('innerHTML').value +'";'+
      // on intake
      '\n\$NElm->model = "'+ document.getElementById('model').value +'";'+
      '\n\$NElm->validatorclass =  "'+ document.getElementById('validatorclass').value +'";'+
      '\n\$NElm->validatormethod =  "'+ document.getElementById('validatormethod').value +'";'+
      '\n\$NElm->sanitizerclass = "'+ document.getElementById('sanitizerclass').value +'";'+
      '\n\$NElm->sanitizermethod = "'+ document.getElementById('sanitizermethod').value +'";';
      
      let namekey = document.getElementById('namekey').value;
      document.getElementById('generatedFormelements').value +=
      '\n\$NElm->schermnaam = "'+ namekey.replace(/#\d*/g,'') +'";'+
      '\n\$'+ document.getElementById('instance').value +
      '->addFormelement( "'+ namekey +'", \$NElm );\n\n';

      let sm = document.getElementsByClassName('sm');
      for( let p=0; p<sm.length; p++ ){ sm[p].disabled = false; }      
    }
    function more()
    {
      let tags = document.getElementsByTagName('input');
      for(let T=2; T<=6; T++)
      {
        if( tags[T].type == "radio" || tags[T].type == "checkbox" )
        { tags[T].checked = false; }
        else{ tags[T].value=""; }
      }
      document.getElementById('multiplechoice').style.display = 'none';
      document.getElementById('elementTag').selectedIndex = 0;
      document.getElementById('innerHTML').value = '';
      document.getElementById('innerHTML').placeholder = 'innerHTML';
      document.getElementById('generatedFormelements').value += "\n";
      
      let sm = document.getElementsByClassName('sm');
      for( let p=0; p<sm.length; p++ ){ sm[p].disabled = "disabled"; }      
    }
    function finish()
    {
      if(confirm('sure?'))
      {
        let instance = '\$'+ document.getElementById('instance').value;
        let gen = "// formcontroller creating scheme\n// necessary for both form setup and data processing\n\n";
        gen += "// at bottom of this segment\n// use either string: "+ instance +".->makeForm() or : "+ instance +".->in() for your purpose. \n\n";
        gen += instance +' = new Formcontroller; \n\n';
        
        gen += instance +'->formattributes = ["'+ 
          document.getElementById('formattributes').value.replace(/=/g,'"=>"').replace(/,/g,'", "') +'"];\n\n';
        gen += document.getElementById('generatedFormelements').value;
        
        document.getElementById('generatedFormelements').value = gen +
          '\n//data intake\n'+
          '//\$'+ document.getElementById('instance').value +'->makeForm();\n\n' +
          '\n//data processing\n'+
          '//\$'+ document.getElementById('instance').value +'->indata();\n\n';
        
        let pr = document.getElementsByClassName('pr');
        for( let p=0; p<pr.length; p++ ){ pr[p].disabled = "disabled"; }      
      }
    }
    function nrnamekey()
    {
      let selElmType = document.getElementsByName('inputtype');
      console.log("selElmType[0].checked "+selElmType[0].checked);
      console.log("selElmType[1].checked "+selElmType[1].checked);
      console.log("selElmType[2].checked "+selElmType[2].checked);
      if( selElmType[1].checked || selElmType[2].checked )
      {
        let namekeyvar = document.getElementById('namekey').value;
                console.log("namekeyvar "+namekeyvar);
        if( window.listLength == undefined){window.listLength = {};}
                console.log("window.listLength "+window.listLength);
        if( window.listLength[namekeyvar] == undefined){ window.listLength[namekeyvar] = 0;}
                console.log("window.listLength[namekeyvar] "+window.listLength[namekeyvar]);
        window.listLength[namekeyvar] ++;
                console.log("window.listLength[namekeyvar] "+window.listLength[namekeyvar]);
        document.getElementById('namekey').value += "#"+ window.listLength[namekeyvar];
                console.log("('namekey').value "+document.getElementById('namekey').value);
      }
    }
    
    var listLength={}; console.log(listLength);
    
  </script>
  <h1>Form generator</h1>to setup your Formcontroller object

  <h2>FORM tag</h2>
  <input type="text" id="instance" placeholder="Formcontroller instance"/><br/>
  nonquoted form-attri=butes, comma separated<br/>
  <input type="text" id="formattributes" placeholder="form-attri=butes,form-attri=butes" value="method=post"/>

  <h2>ELEMENT tags</h2>
  <h3>data intake form</h3>
  <input type="text" id="namekey" placeholder="namekey"/><br/>

  <label for="elementTag">type of element</label><br/>
  <select id="elementTag" 
    onclick="
      let disable = this.selectedIndex == 1 ? false : true ;
      let C = document.getElementsByName('inputtype');
      for(let c=0; c<C.length; c++){C[c].disabled = disable;}
      let displayit = disable ? 'none' : 'block';
      document.getElementById('multiplechoice').style.display = displayit;
      // select //////////////////////////////////////////////////////////
      if(this.selectedIndex == 3)
      {
        let options = 'optionvalue,optionvalue';
        document.getElementById('innerHTML').placeholder = options;
      }
  ">
    <option>element tag type</option>
    <script>
      for(let Ee=0; Ee < elementsTagsList.length; Ee++)
      {
        document.write('\\n\\t'+'<option value="'+elementsTagsList[Ee]+'">'+elementsTagsList[Ee]+'</option>');
      }
    </script>
  </select>
  <br/>
  <div id="multiplechoice" style="display: none;">
  <input type="radio" name="inputtype" value="plain" disabled />plain<br/>
  <input type="radio" name="inputtype" value="radio" disabled 
    onclick="nrnamekey();
      document.getElementById('attributes').value+='type=radio';"
    />radio<br/>
  <input type="radio" name="inputtype" value="checkbox" disabled 
    onclick="nrnamekey();
      document.getElementById('attributes').value+='type=checkbox';"
    />checkbox<br/></div>
  
  nonquoted attri=butes, comma separated<br/>
  <input type="text" id="attributes" placeholder="attri=butes,attri=butes"/><br/>
  <input type="text" id="innerHTML" placeholder="innerHTML" />

  <h3>data processing</h3>
  <input type="text" id="model" placeholder="database model" /><br/>
  <h4>data screening</h4>
  <input type="text" id="validatorclass" placeholder="filepath class" value="SYSROOT/Lib/formFilter.php validizer" /><br/>
  <input type="text" id="validatormethod" value="is_email" /><br/>
  <input type="text" id="sanitizerclass" placeholder="filepath class" value="SYSROOT/Lib/formFilter.php sanitizer" /><br/>
  <input type="text" id="sanitizermethod" value="email" /><br/>
  
  <h2>setup Formcontroller code</h2>
  <button class="pr" onclick="add();">add</button>
  <button class="pr sm" onclick="more();">more</button>
  <button class="sm" onclick="finish();">finish</button>

  <h2>copy / paste into your php script</h2>
  <textarea id="generatedFormelements" cols="96" rows="18"></textarea>
  <script>
    const innerhtml = document.getElementById('innerHTML').innerHTML;
  </script>
</body></html>