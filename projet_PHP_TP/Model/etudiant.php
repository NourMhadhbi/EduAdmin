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
    public static function nbEtudiants()
    {
        require(__DIR__ . "/../Connexion/connexion.php");

        $sql = "
        SELECT COUNT(*) AS nbEtudiant
        FROM utilisateur u
        LEFT JOIN etudiant e ON u.id = e.id
        WHERE u.role = 'Etudiant'
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['nbEtudiant'] ?? 0;
    }
    public static function getById($id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT * FROM etudiant WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            return null; // aucun étudiant trouvé
        }

        return $etudiant;
    }
}
