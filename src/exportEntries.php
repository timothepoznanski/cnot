<?php
@ob_start();
?>
<?php
    include 'functions.php';
    require 'config.php';
    
	include 'db_connect.php';	
?>

<html>

<style type="text/css">
* {
    font-family: 'Calibri';
}
</style>


<head>

	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1"/>
	<title><?php echo JOURNAL_NAME;?></title>

</head>
<body>
            
        <?php
            
            // Downloading the zipped entries

            // Get real path for our folder
            $rootPath = realpath('entries');

            // Initialize archive object
            $zip = new ZipArchive();
            $zip->open('entries.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            // Add the note index
            $indexContent = '<html><head><title>Note Index</title></head><body>';
            $query_right = 'SELECT * FROM entries WHERE trash = 0';
            $res_right = $con->query($query_right);
            while($row = mysqli_fetch_array($res_right, MYSQLI_ASSOC)) {
                $indexContent .= '<a href="./'.$row['id'].'.html">'.$row["heading"].'</a> ('.$row["tags"].')<br>';
            }
            $indexContent .= '</body></html>';
            $zip->addFromString('index.html', $indexContent);

            // Create recursive directory iterator
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Exclude index.php
                    if ($relativePath !== 'index.php') {
                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            // Zip archive will be created only after closing object
            $zip->close();


            $file_url = 'entries.zip';
            ob_clean();
            ob_end_flush();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=entries.zip"); 
            readfile($file_url); 
            // remove zip file is exists in temp path
            unlink($file_url);
            
        ?>
</body>

</html>
