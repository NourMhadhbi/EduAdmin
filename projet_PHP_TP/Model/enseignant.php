<?php
class Enseignant
{
    private $id;
    private $matricule;
    private $specialite;
    public function __construct($matricule, $specialite)
    {
        $this->matricule = $matricule;
        $this->specialite = $specialite;
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
    public static function getAllEnseignants()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "
        SELECT u.id, u.nom, u.prenom, u.email, u.role,
               e.matricule AS etuMatricule,
               e.isActive ,e.specialite,
               u.created_at
        FROM utilisateur u
        LEFT JOIN enseignant e ON u.id = e.id
        WHERE u.role = 'Enseignant'
        ORDER BY u.created_at ASC
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    public static function getById($id) {
           include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT * FROM enseignant WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $enseignant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enseignant) {
            return null; 
        }

        return $enseignant;
    }
}



