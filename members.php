<?php
    require_once "header.php";
    if(!$loggedIn)
    {
        //Stranici pristupa nelogovan korisnik i vrsi se restrikcija
        //header("Location: login.php");
        die("<h3>You must <a href='login.php'>login</a> first to see the content of this page.</h3></div></body></html>");
    }
    
    if (isset($_GET["id"]))
    {
        //Prikazi profil korisnika ciji je id = $_GET["id"]
        $userId = sanitizeString($_GET["id"]);
        $result1 = queryMysql("SELECT first_name, last_name FROM profiles WHERE user_id = $userId");//da li korisnik ima profil
        if ($result1->num_rows)
        {
            $row =  $result1->fetch_assoc();
            $view = $row["first_name"] . " " . $row["last_name"];
        }
        else
        {
            $result2 = queryMysql("SELECT username FROM users WHERE id = $userId");
            $row =  $result2->fetch_assoc();
            $view = $row["username"];
        }

        if ($userId == $id)
        {
            $name = "Your";//obezbedjuje da logovani korisnik moze videti svoj profil
        }
        else
        {
            $name = "${view}'s";
        }

        echo "<h3>$name Profile:</h3>";
        showProfile($userId);
        die("<br><br><a href='members.php'>Go back to the previous page.</div></body></html>");
    }

    if (isset($_GET["add"]))
    {
        $userId = sanitizeString($_GET["add"]);
        //$userID - id korisnika kome se salje zahtev za pracenje
        //$id - id korisnika koji salje zahtev za pracenje
        $result = queryMysql("SELECT * FROM friends WHERE sender_id = $id AND receiver_id = $userId");
        
        if ($result->num_rows == 0)
        {
            $result = queryMysql("INSERT INTO friends(sender_id, receiver_id)
                                VALUES ($id, $userId)");
        }
    }

    if (isset($_GET["remove"]))
    {
       $userId = sanitizeString($_GET["remove"]);
       queryMysql("DELETE FROM friends WHERE sender_id = $id AND receiver_id = $userId");
    }
?>

    <div class="content">
       <h3>Members on the platform:</h3>

       <?php
        //Dohvatam sve korisnike koji nisu logovani korisnik
        $result = queryMysql("SELECT users.id AS uid, users.username, 
            profiles.first_name, profiles.last_name
            FROM users
            LEFT JOIN profiles ON users.id = profiles.user_id
             WHERE users.id != $id
             ORDER BY profiles.first_name, profiles.last_name");
        echo "<ul>";
        while($row = $result->fetch_assoc())
        {
           echo "<li>";
           $userId = $row["uid"];//trenutni id
           echo "<a href='members.php?id=$userId'>";
           echo $row["first_name"];
           echo " ";
           echo $row["last_name"];
           echo " (" . $row["username"];
           echo ")";
           echo "</a>";
           echo "&nbsp;&nbsp;";

           //Proveravamo u kojoj smo relaciji sa korisnikom
           //1) Samo ja drugog korisnika pratim
           //2) Samo drugi korisnik mene prati
           //3) Uzajamno pracenje sa drugim korisnikom

           //Provera da li ja pratim datog korisnika
           $result1 = queryMysql("SELECT * FROM friends WHERE sender_id = $id AND receiver_id = $userId");
           $t1 = $result1->num_rows; // 0 ili 1

           //Provera da li dati korisnik mene prati
           $result2 =  queryMysql("SELECT * FROM friends WHERE sender_id = $userId AND receiver_id = $id");
           $t2 = $result2->num_rows; // 0 ili 1

           $additionalText = "";

           if ($t1 + $t2 > 1)
           {
               echo " is a mutual friend ";
           }
           elseif($t1)
           {    
               echo " you are following ";
           }
           elseif($t2)
           {
               echo " is following you ";
               $additionalText = " back";
           }

           if(!$t1)
           {
            echo "[<a href='members.php?add=$userId'>Follow$additionalText</a>]";
            echo "&nbsp;&nbsp;";
           }
           else
           {
           echo "[<a href='members.php?remove=$userId']>Unfollow</a>]";
           echo "&nbsp;";
           }
           
           echo "[<a href='messages.php?id=$userId'>Send message</a>]";
           echo "</li>"; 
        }
        echo "</ul>";
       ?>

    </div>

</div>
</body>
</html>