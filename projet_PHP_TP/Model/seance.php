<?php
class seance
{
    private $date;
    private $cours_id;
    private $heureDebut;
    private $heureFin;
    public function __construct($date, $cours_id, $heureDebut, $heureFin)
    {
        $this->date = $date;
        $this->cours_id = $cours_id;
        $this->heureDebut = $heureDebut;
        $this->heureFin = $heureFin;
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
    public static function findSeanceByEtudiant($utilisateur_id, $date)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT  s.id,        
   s.date,
    s.heureDebut,
    s.heureFin,
    s.cours_id,i.cours_id,i.etudiant_id,c.titre,c.description,c.enseignant_id,u.nom,u.id as id_enseignant,u.prenom
            FROM seance s
            JOIN inscriptioncours i ON s.cours_id = i.cours_id
            JOIN cours c ON c.id = i.cours_id join utilisateur u on c.enseignant_id =u.id
            WHERE i.etudiant_id = :id 
              AND s.date = :dateA
            ORDER BY  s.heureDebut ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $utilisateur_id,
            ':dateA' => $date
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function findAllSeancesByEtudiant($etudiant_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $query = "
    SELECT s.id, s.date, s.heureDebut, s.heureFin, c.titre AS nomCours, CONCAT(u.prenom, ' ', u.nom) AS nomProf
    FROM seance s
    JOIN cours c ON s.cours_id = c.id
    JOIN inscriptioncours i ON i.cours_id = c.id
    JOIN utilisateur u ON c.enseignant_id = u.id
    WHERE i.etudiant_id = :etudiant_id
    ORDER BY s.date DESC, s.heureDebut ASC
    ";

        $stmt = $conn->prepare($query);
        $stmt->execute([':etudiant_id' => $etudiant_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
