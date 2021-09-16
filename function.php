<?php
    $dbhost = "localhost";
    $dbname = "dmreza1";
    $dbuser = "dmadmin";
    $dbpassword = "123";

    //objekat konekcije ka bazi
    $connection = new mysqli($dbhost, $dbuser, $dbpassword, $dbname);
    if($connection->connect_error != null)
    {
        die($connection->connect_error);
    }

    //funckija za izvrsavanje prozivoljnih upita u bazu podataka
    function queryMysql($query)
    {
        global $connection;
        $result = $connection->query($query);
        if(!$result)
        {
            die($connection->error);
        }
        return $result;
    }

    //funckija za izvrsavanje create table upita
    function createTable($name, $query)
    {
        queryMysql("CREATE TABLE IF NOT EXISTS $name($query)");
        echo "Table '$name' created or already exists.<br>";
    }

    //Funkcija za tretiranje inputa iz forme
    function sanitizeString($text)
    {
        $text = strip_tags($text);
        $text = htmlentities($text);
        $text =  stripslashes($text);
        global $connection;
        $text = $connection->real_escape_string($text);
        return $text;
    }

    //Funkcija za prikaz korisnickog profila
    function showProfile($id)
    {
        $result = queryMysql("SELECT * FROM profiles WHERE user_id = $id");
        if ($result->num_rows)
        {
            $row = $result->fetch_assoc();
            echo "<div class='p'>";
            if (file_exists("profile_images/$id.jpg")) 
            {
                echo "<img src='profile_images/$id.jpg' class='pf'>";
            }
            echo "<div class='pi'>";
            echo $row['first_name'] . " " . $row["last_name"];
            echo "<br>";
            echo $row["email"];
            echo "<br>";
            echo $row["bio"];
            echo "</div>";
            echo "</div>";
        }
    }
?>