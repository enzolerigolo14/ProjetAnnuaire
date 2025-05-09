<?php
function motDePasse($longueur=5) { 
 
 $Chaine = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 

 $Chaine = str_shuffle($Chaine);

 $Chaine = substr($Chaine,0,$longueur);
 
 return $Chaine;
}

echo motDePasse(9); 

?>