<?php
    require_once "header.php";

    if(!$loggedIn)
    {
        die("</div></body></html>");
    }

    if(isset($_GET["id"]))
    {
        $viewId = sanitizeString($_GET["id"]);//id koji prenosimo kao parametar
    }
    else
    {
        $viewId = $id;//vrednost logovanog korisnika, ako nema parametra, njemu se upucuje poruka
    }
    
    //Brisanje poruka
    if(isset($_GET["erase"]))
    {
        $messageId = sanitizeString($_GET["erase"]);
        $result = queryMysql("DELETE FROM messages WHERE id=$messageId");
        if($result)
        {
            echo "<h4>Message deleted!</h4>";
        }
        
    }

    if($viewId == $id)//ako gledam svoje poruke
    {
        $text = "Your messages:";
    }
    else
    {
        $temp = queryMysql("SELECT * FROM users WHERE id = $viewId");
        if($temp->num_rows)
        {
            $row = $temp->fetch_assoc();
            $username = $row["username"];
            $text = "${username}'s Messages:";
        }
        else
        {
            $text = "";
        }
    }
    
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $message = sanitizeString($_POST["message"]);
        if($message != "")
        {
            $pm = substr(sanitizeString($_POST["pm"]), 0, 1);
            $time = time();
            queryMysql("INSERT INTO messages(auth_id, recip_id, pm, time, message)
                VALUE
                ($id, $viewId, '$pm', $time, '$message')");
            echo "<h4>Message sent!</h4>";
        }
        else
        {
            echo "<h4>You cannot sent an empty message!</h4>";
        }
    }

    //forma za slanje
    $result =queryMysql("SELECT id FROM users WHERE id=$viewId");
    if($result->num_rows)
    {
        ?>
        <form action="messages.php?id=<?php echo $viewId ?>" method="POST">
            <h4>Type here to leave a message:</h4>
            <textarea name="message" id="message" cols="60" rows="3"></textarea>
            <br>
            Private <input type="radio" name="pm" id="pm" value="1" checked>
            &nbsp; &nbsp; &nbsp;
            Public <input type="radio" name="pm" id="pm" value="0">
            <br>
            <input type="submit" value="Send message">
        </form>
    
    <?php
    }

    //sve poruke koje je neki korisnik primio
    $result = queryMysql("SELECT * FROM messages WHERE recip_id = $viewId ORDER BY time DESC");
    if($result->num_rows)
    {
         echo "<h3>$text</h3>";
        while($row = $result->fetch_assoc())
        {
            if($row["pm"] == 0 || $row["auth_id"] == $id || $row["recip_id"] == $id)
            {
                echo "<div class='message'>";
                //echo date("M jS \'y g:ia:'", $row["time"]);
                echo date("j.n.Y. H:i:s:", $row["time"]);
                $authId = $row["auth_id"];
                $result1 = queryMysql("SELECT username FROM users WHERE id = $authId");
                $row1 = $result1->fetch_assoc();
                echo $row1["username"];
                if($row["pm"] == '0')
                {
                    echo " wrote: <br>&quot;" . $row["message"] . "&quot;";
                }
                else
                {
                    echo " whispered: <br>&quot;" . $row["message"] . "&quot;";
                }
                if($row["auth_id"] == $id)
                {
                    $messageId = $row["id"];
                    echo "<br><a href='messages.php?id=${viewId}&erase=$messageId'>Delete message</a>";
                }
                echo "</div>";
            }
        }
    }
    else
    {
        echo "<h3>No messages yet.</h3>";
    }

/*<div>
</body>
</html>*/