<?php 
if(isset($_GET['id']))
{
    $id_nhan= $_GET['id'];
    echo "<h1>id nhan dc la</h1>";
    echo "<p>$id_nhan</p>";
}
else
echo "LOI";




?>