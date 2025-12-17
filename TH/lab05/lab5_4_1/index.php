<?php
$arr   = [];
$r     = ["id" => 1, "name" => "Product1"];
$arr[] = $r;
$r     = ["id" => 2, "name" => "Product2"];
$arr[] = $r;
$r     = ["id" => 3, "name" => "Product3"];
$arr[] = $r;
$r     = ["id" => 4, "name" => "Product4"];
$arr[] = $r;
$file  = "2.php";
echo "<h1>Danh sach san pham</h1>";
echo "<ul>";
foreach ($arr as $product) {
    $id   = $product['id'];
    $name = $product['name'];
    echo "<li><a href=\"$file?id=$id\">$name</a></li>";

}
echo "</ul>";
