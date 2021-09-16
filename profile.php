<?php
    require_once "header.php";
    if(!$loggedIn)
    {
        //Stranici pristupa nelogovan korisnik i vrsi se restrikcija
        //header("Location: login.php");
        die("<h3>You must <a href='login.php'>login</a> first to see the content of this page.</h3></div></body></html>");
    }

    $fname = $lname = $email = $gender = $lang = $bio = "";
    $fnameError = $lnameError = $emailError = $genderError = $langError = $imageError = "";

    $result = queryMysql("SELECT * FROM profiles WHERE user_id = $id");
    if($result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
        $fname = $row['first_name'];
        $lname = $row['last_name'];
        $email = $row['email'];
        $gender = $row['gender'];
        $lang = $row['language'];
        $bio = $row['bio'];
    }

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if(!empty($_POST["fname"]))
        {
            $fname = sanitizeString($_POST["fname"]);
        }
        else
        {
            $fnameError = "First name cannot be left blank.";
        }
        if(!empty($_POST["lname"]))
        {
            $lname = sanitizeString($_POST["lname"]);
        }
        else
        {
            $lnameError = "Last name cannot be left blank.";
        }
        if(empty($_POST["email"]))//Da li je vrednost elementa prazna?
        {
            $emailError = "Email field cannot be blank.";
        }
        else
        {
            $email = sanitizeString($_POST["email"]);
            //provera da li je email adresa dobra
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $emailError = "Invalid email format.";
                $email = "";
            }
        }
        if(isset($_POST["gender"]))//Da li postoji element sa zadatim kljucem?
        {
            $gender = sanitizeString($_POST["gender"]);//Moze i ne mora
        }
        else
        {
            $genderError = "Gender cannot be left blank.";
        }
        if(!empty($_POST["lang"]))
        {
            $lang = sanitizeString($_POST["lang"]);
        }
        else
        {
            $langError = "You must choose one language.";
        }
         $bio = sanitizeString($_POST["bio"]);
         $bio = preg_replace('/\s\s+/', ' ', $bio);

         //Upis u bazu ako nije doslo do greske
         if($fnameError == "" && $lnameError == "" && $emailError == "" && $genderError == "" && $langError == "")
        {
            if($result->num_rows > 0)
            {
                //Profil vec postoji
                queryMysql("UPDATE profiles
                    SET first_name = '$fname',
                        last_name = '$lname',
                        email = '$email',
                        gender = '$gender',
                        language = '$lang',
                        bio = '$bio'
                    WHERE user_id = $id"
                );
            }
            else
            {
                queryMysql("INSERT INTO profiles(user_id, first_name, last_name, email, gender, language, bio)
                    VALUES($id, '$fname', '$lname', '$email', '$gender', '$lang', '$bio')"
                    );
            }
        }
    }

    //Da li je korinski poslao slicicu
    if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
    {
        //Da li posotiji za profilne slicice i ako ne postoji, napravi ga
        if(!file_exists("profile_images/"))
        {
            mkdir("profile_images/", 0777, true);
        }

        //Svaki korisnik ima svoju sliku u formatu id.jpg
        $saveTo = "profile_images/$id.jpg";
        //Prebacuje se slicica iz privremene lokaciju u folderu projekta
        move_uploaded_file($_FILES["image"]["tmp_name"], $saveTo);
        //Redimenzioniranje slicice
        //1)Provra ekstenzije datoteke i kreiranje nove slike na osnovu poslate iz forma
        $typeOk =  true;
        switch ($_FILES["image"]["type"]) 
        {
            case 'image/gif':
                $src = imagecreatefromgif($saveTo);
                break;
            case 'image/jpeg':
            case 'image/jpg':
                $src = imagecreatefromjpeg($saveTo);
                break;
            case 'image/png':
                $src = imagecreatefrompng($saveTo);
                break;
            
            default:
                $typeOk = false;
                break;
        }

        if (!$typeOk) 
        {
            $imageError - "Allowed types for profile photo are: gif/jpeg/jpg/png!";
        }
        else
        {
            //2)Menjamo dimenzije nove slike
            list($w, $h) = getimagesize($saveTo);

            $max = 100;
            $tw = $w;//nova sirina
            $th = $h;//nova visina
            if($w > $h && $w > $max)
            {
                $th = $max * $h / $w;
                $tw =  $max;
            }
            else if($h > $w && $h > $max)
            {
                $tw = $max * $w / $h;
                $th = $max;
            }
            else
            {
                $th = $tw = $max;
            }

            //3)Kreiranje nove slicice sa zadatim dimenzijama ($tw x $th)
            $tmp = imagecreatetruecolor($tw, $th);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
            imageconvolution($tmp, array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1)), 8, 0);
            imagejpeg($tmp, $saveTo);
            imagedestroy($src);
            imagedestroy($tmp);
        }
    }

    showProfile($id);

?>

    <div class="content">
        <!-- Prikazujemo sadrzaj stranice profile.php -->
        <form action="profile.php" method="POST" enctype="multipart/form-data">
        <h2>Add or edit your profile</h2>
        <span class="error">* required fields</span><br><br>
            <label for="fname">First name:</label>
            <input type="text" name="fname" id="fname" value="<?php echo $fname ?>">
            <span class="error">*<?php echo $fnameError ?></span>
            <br>
            <label for="lname">Last name:</label>
            <input type="text" name="lname" id="lname" value="<?php echo $lname ?>">
            <span class="error">*<?php echo $lnameError ?></span>
            <br>
            <label for="email">Email:</label>
            <input type="text" name="email" id="email" value="<?php echo $email ?>">
            <span class="error">*<?php echo $emailError ?></span>
            <br>
            <label for="gender">Gender:</label>
            <input type="radio" name="gender" id="female" value="female"
            <?php echo ($gender == 'female') ? "checked" : "" ?>> Female
            <input type="radio" name="gender" id="male" value="male"
            <?php echo ($gender == 'male') ? "checked" : "" ?>> Male
            <span class="error">*<?php echo $genderError ?></span>
            <br>
            <label for="lang">Favourite programming language</label>
            <select name="lang" id="lang">
                <option value=""<?php echo ($lang == "") ? "selected" : "" ?>>--Choose--</option>
                <option value="php" <?php echo ($lang == "php") ? "selected" : "" ?>>Php</option>
                <option value="c" <?php echo ($lang == "c") ? "selected" : "" ?>>C</option>
                <option value="c++" <?php echo ($lang == "c++") ? "selected" : "" ?>>C++</option>
                <option value="java" <?php echo ($lang == "java") ? "selected" : "" ?>>Java</option>
            </select>
            <span class="error">* <?php echo $langError ?></span>
            <br>
            <label for="bio">Biography:</label>
            <textarea name="bio" id="bio" cols="30" rows="4"><?php echo $bio ?></textarea>
            <br>
            <label for="image">Profile photo:</label>
            <input type="file" name="image" id="image">
            <span class="error"><?php echo $imageError ?></span>
            <br>
            <input type="submit" value="Save Profile">
        </form>
    </div>

</div>
</body>
</html>