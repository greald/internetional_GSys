<?php
// controller supplied $data here
?>

    <body>
    <?php
      Indent::T();
      $includes[0]->view();
      Indent::T(-1);
    ?> 
    </body>