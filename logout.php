<?php
    require_once "header.php";
    if(isset($_SESSION["username"]))
    {
        $_SESSION = array();
        session_destroy();
        echo "<div class='content'>You have been logged out. Go to the 
            <a href='index.php'>main page</a>.</div>";
    }
    else
    {
        echo "<div class='content'>You cannot logout because you are not logged in!</div>";
    }

?>

</div>
</body>
</html>