<?php
    //ukljucujemo header.php
    require_once "header.php";
    require_once "PrivilegedUser.php";
?>
        <div class="content">
            <p>
            <?php
            echo "Welcome, $user!";
            if (isset($id))
            {
                showProfile($id);
               /* $result = PrivilegedUser::getByUsername($_SESSION["username"]);
                var_dump($result);*/
            }
            /*
            $result = Role::getRolePerms(1);
            var_dump($result);
            var_dump($result->hasPermission("Run SQL"));
            */
            ?>
           </p>

      
        </div>  
    </div>
 
</body>
</html>