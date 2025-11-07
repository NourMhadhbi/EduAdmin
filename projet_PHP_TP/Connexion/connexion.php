<?php


try {
    $conn = new PDO('mysql:host=localhost;dbname=dbonlearn', 'root', '');
    //pour les lettres (é,à,è)qui affiche par le symbole ? 
    // Définir l'encodage des caractères avec une requête SQL
    $conn->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}
?>