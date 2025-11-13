<?php
class etudiant
{
    private $id;
    private $matricule;
    public function __construct($matricule)
    {
        $this->matricule = $matricule;
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
    public static function getAllEtudiants()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "
        SELECT u.id, u.nom, u.prenom, u.email, u.role,
               e.matricule AS etuMatricule,
               e.isActive ,
               u.created_at
        FROM utilisateur u
        LEFT JOIN etudiant e ON u.id = e.id
        WHERE u.role = 'Etudiant'
        ORDER BY u.created_at ASC
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
}
