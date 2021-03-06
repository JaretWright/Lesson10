<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saving Album...</title>
</head>

<body>
<?php
    $albumID = $_POST['albumID'];
    $title = $_POST['title'];
    $year = $_POST['year'];
    $artist = $_POST['artist'];
    $genre = $_POST['genre'];
    $coverFileName = $_FILES['coverFile']['name'];
    $coverFileType = $_FILES['coverFile']['type'];
    $coverFileTmpLocation = $_FILES['coverFile']['tmp_name'];

    //check if the $fileName is null, but there is an entry in the DB
    if (!empty($albumID) && empty($coverFileName))
    {
        require ('db.php');
        $sql = "SELECT coverFile FROM albums WHERE albumID = :albumID";
        $cmd = $conn->prepare($sql);
        $cmd->bindParam(':albumID',$albumID, PDO::PARAM_INT);
        $cmd->execute();
        $file = $cmd->fetch();
        $fileName = $file['coverFile'];
    }
    else {
        //Check to ensure that the file uploaded is an image
        $validFileTypes = ['image/jpg', 'image/png', 'image/svg', 'image/gif', 'image/jpeg'];
        $fileType = mime_content_type($coverFileTmpLocation);

        //store the file on our server
        if (in_array($fileType, $validFileTypes)) {
            $fileName = "uploads/" . uniqid("", true) . "-" . $coverFileName;
            move_uploaded_file($coverFileTmpLocation, $fileName);
        }
    }

    //step 1 - connect to the database
    require_once ('db.php');

    //step 2 - create the SQL command to INSERT or UPDATE a record
    if (!empty($albumID)){
        $sql = "UPDATE albums  
                   SET title = :title,
                       year = :year,
                       artist = :artist,
                       genre = :genre,
                       coverFile = :coverFile
                WHERE albumID = :albumID";}
    else {
        $sql = "INSERT INTO albums (title,   year,  artist,  genre, coverFile) 
                        VALUES (:title, :year, :artist, :genre, :coverFile);";
    }

    //step 3 - prepare the SQL command and bind the arguments to prevent SQL injection
    $cmd = $conn->prepare($sql);
    $cmd->bindParam(':title', $title, PDO::PARAM_STR, 50);
    $cmd->bindParam(':year', $year, PDO::PARAM_INT, 4);
    $cmd->bindParam(':artist', $artist, PDO::PARAM_STR, 50);
    $cmd->bindParam(':genre', $genre, PDO::PARAM_STR, 20);
    $cmd->bindParam(':coverFile',$fileName, PDO::PARAM_STR, 100);

    if (!empty($albumID))
        $cmd->bindParam(':albumID', $albumID, PDO::PARAM_INT);

    //step 4 - execute
    $cmd->execute();

    //step 5 - disconnect from database
    $conn = null;

    //step 6 - redirect to the albums page
    header('location:albums.php');
?>
</body>

</html>
