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
        echo "<ul id='member_list'>";
        while($row = $result->fetch_assoc())
        {
           $userId = $row["uid"];//trenutni id, korinsku kome saljemo ili brisemo zahtev
           echo "<li id='$userId'>";//menjamo tu stavku liste
           echo "<a href='members2.php?id=$userId'>";
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
           {//mid ko salje zahtev, fid kome je upucen zahtev
            echo "[<a mid='$id' fid='$userId' href='#' class='add'>Follow$additionalText</a>]";
            echo "&nbsp;&nbsp;";
           }
           else
           {
           echo "[<a mid='$id' fid='$userId' href='#' class='remove'>Unfollow</a>]";
           echo "&nbsp;";
           }
           
           echo "[<a href='messages.php?id=$userId'>Send message</a>]";
           echo "</li>"; 
        }
        echo "</ul>";
       ?>
    </div>

    <script src="myscript.js"></script>
    <script>
        //var addLinks = document.getElementsByClassName("add");
      /*  var addLinks = document.querySelectorAll('.add');//node list linkova koji imaju add klasu
        for (let i = 0; i < addLinks.length; i++) 
        {
           //console.log(addLinks[i]);
           addLinks[i].addEventListener("click", function(event) {
               event.preventDefault();//ne prati link koji je naveden u hrefu
               var myId = this.getAttribute('mid');//this je addLinks, od njega pokupi atribut
               var friendId = this.getAttribute('fid');

               var params = "action=add&my_id=" + myId + "&friend_id=" + friendId;//kao preko url da saljemo
               var request = ajaxRequest();
               if(request !== false) {
                request.open("POST", "manage_friend.php", true);//str ka kojoj saljemo zahtev
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.setRequestHeader("Content-lenght", params.length);//prosledjujemo parametar za koji proveravamo u bazi
                request.setRequestHeader("Connection", "close");

                
                request.onreadystatechange = function() {
                    if(this.readyState == 4 && this.status == 200) {//sve je u redu
                        //u html delu ko stvke li hocemo da menjamo userId, sto znaci
                        //da cemo ovde da menjamo friendId
                        document.getElementById(friendId).innerHTML =  this.responseText;
                    }
                }

                request.send(params);//tek sad saljemo, ajax poziv
                } 
            });
        }

        var removeLinks = document.querySelectorAll('.remove');//node list linkova koji imaju add klasu
        for (let i = 0; i < removeLinks.length; i++) 
        {
           //console.log(removeLinks[i]);
           removeLinks[i].addEventListener("click", function(event) {
               event.preventDefault();//ne prati link koji je naveden u hrefu
               var myId = this.getAttribute('mid');//this je addLinks, od njega pokupi atribut
               var friendId = this.getAttribute('fid');

               var params = "action=remove&my_id=" + myId + "&friend_id=" + friendId;//kao preko url da saljemo
               var request = ajaxRequest();
               if(request !== false) {
                request.open("POST", "manage_friend.php", true);//str ka kojoj saljemo zahtev
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.setRequestHeader("Content-lenght", params.length);//prosledjujemo parametar za koji proveravamo u bazi
                request.setRequestHeader("Connection", "close");

                
                request.onreadystatechange = function() {
                    if(this.readyState == 4 && this.status == 200) {//sve je u redu
                        //u html delu ko stvke li hocemo da menjamo userId, sto znaci
                        //da cemo ovde da menjamo friendId
                        document.getElementById(friendId).innerHTML =  this.responseText;
                    }
                }

                request.send(params);//tek sad saljemo, ajax poziv
                } 
            });
           
        }*/
        
        
        //css selektor ovde ide pa pisemo #

        
        var ulList = document.querySelector("#member_list");//pokuplja jedan element
        ulList.addEventListener("click", function(event) {
            //console.log(event);
            if(event.target.tagName == "A")//Da li smo kliknuli na link
            {
                if(event.target.className == "add")//saljemo zahtev
                {
                    event.preventDefault();
                    var myId = event.target.getAttribute('mid');//this je addLinks, od njega pokupi atribut
                    var friendId = event.target.getAttribute('fid');

                    var params = "action=add&my_id=" + myId + "&friend_id=" + friendId;//kao preko url da saljemo
                    var request = ajaxRequest();
                    if(request !== false) {
                        request.open("POST", "manage_friend.php", true);//str ka kojoj saljemo zahtev
                        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        request.setRequestHeader("Content-lenght", params.length);//prosledjujemo parametar za koji proveravamo u bazi
                        request.setRequestHeader("Connection", "close");

                        
                        request.onreadystatechange = function() {
                            if(this.readyState == 4 && this.status == 200) {//sve je u redu
                                //u html delu ko stvke li hocemo da menjamo userId, sto znaci
                                //da cemo ovde da menjamo friendId
                                document.getElementById(friendId).innerHTML =  this.responseText;
                            }
                        }

                        request.send(params);//tek sad saljemo, ajax poziv
                        } 
                    } 
                    else if(event.target.className == "remove")//saljemo zahtev
                    {
                        //alert("izbrisi");
                        event.preventDefault();
                        var myId = event.target.getAttribute('mid');//this je addLinks, od njega pokupi atribut
                        var friendId = event.target.getAttribute('fid');

                        var params = "action=remove&my_id=" + myId + "&friend_id=" + friendId;//kao preko url da saljemo
                        var request = ajaxRequest();
                        if(request !== false) {
                            request.open("POST", "manage_friend.php", true);//str ka kojoj saljemo zahtev
                            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            request.setRequestHeader("Content-lenght", params.length);//prosledjujemo parametar za koji proveravamo u bazi
                            request.setRequestHeader("Connection", "close");

                            
                            request.onreadystatechange = function() {
                                if(this.readyState == 4 && this.status == 200) {//sve je u redu
                                    //u html delu ko stvke li hocemo da menjamo userId, sto znaci
                                    //da cemo ovde da menjamo friendId
                                    document.getElementById(friendId).innerHTML =  this.responseText;
                                }
                            }

                            request.send(params);//tek sad saljemo, ajax poziv
                        } 
                    }
                }
        });

    </script>


</div>
</body>
</html>