<?php
// controller supplied $data here
?>
  <html>
  <?php Indent::T();
    echo Indent::T(0)."<!-- ".$title." -->".Indent::T(0);
  //echo "\n<br>".__FILE__.__LINE__." defined_vars\n" ; var_dump($includes[0]);
    $includes[0]->view();
    $includes[1]->view();
    echo Indent::nT(0)."<!-- ".$title." -->".Indent::T(-1);
    ?>
    
  </html>
  <?php Indent::T(-1); ?>