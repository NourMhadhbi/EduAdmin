<?php
class inscriptionCours
{
    private $etudiant_id;
    private $cours_id;
    public function __construct($etudiant_id, $cours_id)
    {
        $this->etudiant_id = $etudiant_id;
        $this->cours_id = $cours_id;
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
    public static function inscriptionCours($etudiant_id, $cours_id)
    {
        include("../Connexion/connexion.php");

        try {
            $check = $conn->prepare("
            SELECT 1 
            FROM inscriptioncours 
            WHERE etudiant_id = :etudiant_id AND cours_id = :cours_id
        ");
            $check->execute([
                ":etudiant_id" => $etudiant_id,
                ":cours_id" => $cours_id
            ]);

            if ($check->fetch()) {
                return "existe";
            }
            $req = $conn->prepare("
            INSERT INTO inscriptioncours (etudiant_id, cours_id)
            VALUES (:etudiant_id, :cours_id)
        ");
            $req->execute([
                ":etudiant_id" => $etudiant_id,
                ":cours_id" => $cours_id
            ]);

            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public static function getEtudiantsByCour($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "SELECT u.* FROM inscriptioncours ic 
                  JOIN utilisateur u ON ic.etudiant_id = u.id 
                  WHERE ic.cours_id = :cours_id";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur getEtudiantsByCour: " . $e->getMessage());
            return [];
        }
    }
}
