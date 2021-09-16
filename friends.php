<?php
    require_once "header.php";

    if (!$loggedIn)
    {
        die("</div></body></html>");
    }


echo "<div class='content'>";
    $followers = array(); // id-evi korisnika koje mene prate
    $following = array(); // id-evi korisnika koje ja pratim

    $result =  queryMysql("SELECT sender_id FROM friends WHERE receiver_id = $id"); //mene prate
    while($row = $result->fetch_assoc())
    {
        $followerId = $row["sender_id"];
        $followers[] = $followerId;//na kraju niza 
    }

    $result1 = queryMysql("SELECT receiver_id FROM friends WHERE sender_id = $id");//ja pratim
    while($row = $result1->fetch_assoc())
    {
        $followingId = $row["receiver_id"];
        $following[] = $followingId;
    }

    $mutual = array_intersect($followers, $following);
    $followers = array_diff($followers, $mutual);
    $following = array_diff($following, $mutual);

    $haveFriends = false;//da li ima prijatelja

    if(sizeof($mutual))
    {
        $haveFriends = true;
        echo "<h3>Mutual friends</h3>";
        echo "<ul>";
        foreach($mutual as $friendId)
        {
            $result = queryMysql("SELECT users.id AS uid, users.username, 
                profiles.first_name, profiles.last_name
                FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row["username"];
            $fName = $row["first_name"];
            $lName = $row["last_name"];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";
        }
        echo "</ul>";
    }

    if(sizeof($followers))
    {
        $haveFriends = true;
        echo "<h3>Friends who are following you</h3>";
        echo "<ul>";
        foreach($followers as $friendId)
        {
            $result = queryMysql("SELECT users.id AS uid, users.username, 
                profiles.first_name, profiles.last_name
                FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row["username"];
            $fName = $row["first_name"];
            $lName = $row["last_name"];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";
        }
        echo "</ul>";
    }

    if(sizeof($following))
    {
        $haveFriends = true;
        echo "<h3>Friends you're following</h3>";
        echo "<ul>";
        foreach($following as $friendId)
        {
            $result = queryMysql("SELECT users.id AS uid, users.username, 
                profiles.first_name, profiles.last_name
                FROM users
                LEFT JOIN profiles ON users.id = profiles.user_id
                WHERE users.id = $friendId");
            $row = $result->fetch_assoc();
            $username = $row["username"];
            $fName = $row["first_name"];
            $lName = $row["last_name"];
            echo "<li><a href='members.php?id=$friendId'>$fName $lName ($username)</a></li>";
        }
        echo "</ul>";
    }

    if(!$haveFriends)
    {
        echo "<div>You don't have any friends yet. :(</div>";
        echo "<div><a href='members.php'>Go make some friends! :)</a></div>";
    }


echo "</div>";

?>

</div>
</body>
</html>