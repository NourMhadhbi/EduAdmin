<?php
class Cours {
       private $id;
    private $titre;
    private $description;
    private $enseignant_id;
    public function __construct($titre, $description,$enseignant_id)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->enseignant_id = $enseignant_id;
    }
    public function __get($attr)
    {
        if (!isset($this->$attr))
            return "erreur";
        else
            return ($this->$attr);
    }
    public function __set($attr, $value)
    {
        $this->$attr = $value;
    }
    public function __toString()
    {
        $s = "";
        return $s;
    }
    public static function getAllCours() {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT id, titre FROM cours ORDER BY titre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}