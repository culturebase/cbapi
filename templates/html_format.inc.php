<!DOCTYPE html>
<html>
   <head>
      <title><?php $this->title();?></title>
      <?php $this->javascript(); ?>
   </head>
   <body>
      <?php
         if (!$this->debug()) echo '<noscript>';
         $this->content();
         if (!$this->debug()) echo '</noscript>';
      ?>
   </body>
</html>

