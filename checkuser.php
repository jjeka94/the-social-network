<?php
     require_once "function.php";

     if(isset($_POST["username"]))
     {
         $username = sanitizeString($_POST["username"]);
         $result = queryMysql("SELECT * FROM users WHERE username ='$username'");
         if($result->num_rows)
         {
             echo "<spam class='taken'>That username is taken - please choose another one!</spam>";
         }
         else
         {
             echo "<spam class='available'>This username is available</span>";
         }
     }

?>