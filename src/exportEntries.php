<?php
@ob_start();
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
            $rootPath = realpath('entries');
            $zip = new ZipArchive();
            $zip->open('entries.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $indexContent = '<html><head><title>Note Index</title></head><body>';
            $query_right = 'SELECT * FROM entries WHERE trash = 0';
            $res_right = $con->query($query_right);
            while($row = mysqli_fetch_array($res_right, MYSQLI_ASSOC)) {
                $indexContent .= '<a href="./'.$row['id'].'.html">'.$row["heading"].'</a> ('.$row["tags"].')<br>';
            }
            $indexContent .= '</body></html>';
            $zip->addFromString('index.html', $indexContent);

            // Create recursive directory iterator
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    if ($relativePath !== 'index.php') {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();

            $file_url = 'entries.zip';
            ob_clean();
            ob_end_flush();
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=entries.zip"); 
            readfile($file_url); 
            unlink($file_url);
            
        ?>
</body>

</html>
