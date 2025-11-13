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

    public static function ajouterCours($titre, $description, $enseignant_id)
    {
        include("../Connexion/connexion.php");

        try {
            $req = $conn->prepare("
                INSERT INTO cours (titre, description, enseignant_id)
                VALUES (:titre, :description, :enseignant_id)
            ");
            $req->bindParam(':titre', $titre);
            $req->bindParam(':description', $description);
            $req->bindParam(':enseignant_id', $enseignant_id);
            $req->execute();

            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
