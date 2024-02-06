<?php

?>
<!DOCTYPE html>
<html>
  <head>
    <script>
      const ids = ["viewpath","viewfile","viewdata","embedded"];
      function sibling()
      {
        // $head = new View( APPROOT."/Page/head", ["title"=>"Tale","font_size"=>"3vh","background_color"=>"#fedcba"] );
        let viewpath = document.getElementById('viewpath').value;
        viewpath += viewpath == "" ? "" : "/";
        let viewfile = document.getElementById('viewfile').value;
        let viewdata = '["' + document.getElementById('viewdata').value.replace(/=/g,'"=>"').replace(/,/g,'", "') + '"]';
        viewdata = viewdata == '[""]' ? '[]' : viewdata;
        return "\$"+viewfile+ ' = new View( APPROOT."/' +viewpath+viewfile+ '", '+viewdata+' );';
      }
      function embed()
      {
        let nests = '[["' + document.getElementById('embedded').value.replace(/=/g,'"=>"').replace(/,/g,'"], ["') + '"]]';
        let embed = 'foreach('+nests+' as \$nest ){ \$par= key(\$nest); \$chi = \$nest[\$par]; \$\$par = \$\$par->nest(\$\$chi); }';
        return embed;
      }
      function clearfields()
      {
        for ( let i=0; i< ids.length; i++ ) {
          document.getElementById(ids[i]).value = '';
        }
      }
    </script>
  </head>
  <body>
    <h1>page setup</h1>
    Structure your project's pages from View modules, either nested or not.
    <div id="quest">
      <h2>modules</h2>
      APPROOT/<input id="viewpath" placeholder="path/to" />/
      <input id="viewfile" placeholder="view file name" />.view.php
      <br/>
      view=data pairs, comma separated
      <input id="viewdata" placeholder="view=data,view=data" />
      <br/>
      <button onclick="document.getElementById('pagegenerator').value += sibling() + '\n'; clearfields();">module</button>
      <h2>Arrange modules in nests</h2>
      <ul><li>module=childview pairs, comma separated
        </li><li>WITHIN parent module: <b>in order</b> of appearance
        </li><li>BETWEEN sibling modules: in <b>reversed</b> order of appearance
        </li><li><pre style="color:#555555;">E.g: html=head,html=body,doctype=html</pre>
        </li></ul>
        <input id="embedded" placeholder="module=childview,module=childview" />
      <button onclick="document.getElementById('pagegenerator').value += '\n' + embed();">finish</button>
    </div>
    <h2>Copy & paste into your project's base controller</h2>
    <textarea id="pagegenerator" cols="96" rows="18"></textarea>
  </body>
</html>