<?php
require_once "connexion.php";
if (isset($_GET["id"])){
    $sqlprof="select * from professeurs where prof_id=".$_GET["id"];
    $result=$conn->query($sqlprof);
    
    $code=$_GET["id"];
    $sql="DELETE FROM professeurs WHERE prof_id=$code";
    $result=$conn->query($sql);
    if ($result){
        header("location:professeurs.php");
    }

  
}
?>