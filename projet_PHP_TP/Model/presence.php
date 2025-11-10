<?php
class Presence
{
    private $etudiant_id;
    private $seance_id;
    private $statut;
    private $imageCapture;

    public function __construct($etudiant_id, $seance_id, $statut, $imageCapture)
    {
        $this->etudiant_id = $etudiant_id;
        $this->seance_id = $seance_id;
        $this->statut = $statut;
        $this->imageCapture = $imageCapture;
    }


    public function ajouterPresence($etudiant_id, $seance_id, $statut, $imageCapture)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $checkSql = "SELECT COUNT(*) as count 
                 FROM presence 
                 WHERE etudiant_id = :etudiant_id 
                   AND seance_id = :seance_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id
        ]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {

            return false;
        }
        $sql = "INSERT INTO presence (etudiant_id, seance_id, statut, imageCapture) 
                VALUES (:etudiant_id, :seance_id, :statut, :imageCapture)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id,
            ':statut' => $statut,
            ':imageCapture' => $imageCapture
        ]);
    }
    public static function getStatsByEtudiant($etudiant_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        // Total de cours suivis
        $sqlCours = "SELECT COUNT(DISTINCT seance_id) as totalCours FROM presence WHERE etudiant_id = :etudiant_id";
        $stmt = $conn->prepare($sqlCours);
        $stmt->execute([':etudiant_id' => $etudiant_id]);
        $totalCours = $stmt->fetch(PDO::FETCH_ASSOC)['totalCours'] ?? 0;

        // Présences
        $sqlPresences = "SELECT COUNT(*) as nbPresence FROM presence WHERE etudiant_id = :etudiant_id AND statut = 'Présent'";
        $stmt = $conn->prepare($sqlPresences);
        $stmt->execute([':etudiant_id' => $etudiant_id]);
        $nbPresence = $stmt->fetch(PDO::FETCH_ASSOC)['nbPresence'] ?? 0;

        // Absences
        $sqlAbsences = "SELECT COUNT(*) as nbAbsence FROM presence WHERE etudiant_id = :etudiant_id AND statut = 'Absent'";
        $stmt = $conn->prepare($sqlAbsences);
        $stmt->execute([':etudiant_id' => $etudiant_id]);
        $nbAbsence = $stmt->fetch(PDO::FETCH_ASSOC)['nbAbsence'] ?? 0;

        // Calcul du taux de présence
        $tauxPresence = ($nbPresence + $nbAbsence) > 0
            ? round(($nbPresence / ($nbPresence + $nbAbsence)) * 100)
            : 0;

        return [
            'tauxPresence' => $tauxPresence,
            'nbPresence' => $nbPresence,
            'nbAbsence' => $nbAbsence,
            'totalCours' => $totalCours
        ];
    }
    public static function getHistoriqueByEtudiant($etudiant_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $query = "
        SELECT s.date, s.heureDebut, s.heureFin, c.titre, 
               CONCAT(u.prenom, ' ', u.nom) AS nomProf,  p.statut,p.seance_id
        FROM presence p
        JOIN seance s ON p.seance_id = s.id
            JOIN inscriptioncours i ON s.cours_id = i.cours_id
            JOIN cours c ON c.id = i.cours_id join utilisateur u on c.enseignant_id =u.id
    
        WHERE p.etudiant_id = :etudiant_id
        ORDER BY s.date DESC, s.heureDebut ASC
    ";
        $stmt = $conn->prepare($query);
        $stmt->execute([':etudiant_id' => $etudiant_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
