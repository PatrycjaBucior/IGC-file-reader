<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title> IGC files </title>
        <!-- This page is an example of igc class usage -->
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>
        <br/><br/>
        <div id="box">
            <div id="block">
                <br/><br/><br/>
                <div id="form">
                    <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method = "post">
                        <label for = "file">Link to your .igc file:</label><br/>
                        <input type = "text" name = "file" />
                        <button type = "submit">Submit</button>
                    </form>
                </div>
                <br/><br/>
                <?php
                if(isset($_POST['file']) && !empty($_POST['file']) && filter_var($_POST['file'], FILTER_VALIDATE_URL))
                {
                    require("igc.php");
                    $file = $_POST['file'];
                    $igc = new igc($file);
                    $igc->readRecords();
                    echo $igc->htmlInfo(); ?>
            </div>
                    <?php
                    echo $igc->getMap('AIzaSyDS2tdnqz_Sx7rJlDvPwHt3W3gHA_1IQuI', 940, 580);
                    } ?>
            <div id="empty"></div>
        </div>
    </body>

</html>




