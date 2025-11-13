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
    public static function getTodayPresenceStatistics()
{
    include(__DIR__ . "/../Connexion/connexion.php");
    date_default_timezone_set('Africa/Tunis');
    $today = date('Y-m-d');
    try {
        $query = "
            SELECT 
                p.statut,
                COUNT(*) as count
            FROM presence p
            INNER JOIN seance s ON p.seance_id = s.id
            WHERE s.date = :today
            GROUP BY p.statut
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $present = 0;
        $absent = 0;
        $enAttente = 0;
        foreach ($results as $row) {
            switch ($row['statut']) {
                case 'Présent':
                    $present = $row['count'];
                    break;
                case 'Absent':
                    $absent = $row['count'];
                    break;
                case 'EnAttente':
                    $enAttente = $row['count'];
                    break;
            }
        }
        $total = $present + $absent + $enAttente;
        $attendanceRate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        
        return [
            'present' => $present,
            'absent' => $absent,
            'enAttente' => $enAttente,
            'total' => $total,
            'attendanceRate' => $attendanceRate
        ];
        
    } catch (PDOException $e) {
        return [
            'present' => 0,
            'absent' => 0,
            'enAttente' => 0,
            'total' => 0,
            'attendanceRate' => 0
        ];
    }
}
public static function getWeeklyPresenceRate()
{
    include(__DIR__ . "/../Connexion/connexion.php");

    // Requête SQL (ne renvoie que les jours où il y a eu des présences)
    $query = "
        SELECT 
            DATE(s.date) AS date_jour,
            DAYNAME(s.date) AS jour_nom,
            SUM(CASE WHEN LOWER(p.statut) = 'présent' THEN 1 ELSE 0 END) AS presents,
            COUNT(*) AS total
        FROM presence p
        JOIN seance s ON p.seance_id = s.id
        WHERE YEARWEEK(s.date, 1) = YEARWEEK(CURDATE(), 1)
        GROUP BY s.date
        ORDER BY s.date
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 📅 Liste complète des jours de la semaine (lundi → dimanche)
    $jours_semaine = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    // Initialiser les données à 0 pour tous les jours
    $data = array_fill_keys($jours_semaine, 0);

    // Remplir avec les taux trouvés
    foreach ($rows as $row) {
        $jour_nom = $row['jour_nom'];
        $rate = $row['total'] > 0 ? round(($row['presents'] / $row['total']) * 100, 2) : 0;
        $data[$jour_nom] = $rate;
    }

    // 🔄 Conversion en français dans le bon ordre
    $jours = [];
    $taux = [];
    foreach ($jours_semaine as $index => $jour_en) {
        $jours[] = $jours_fr[$index];
        $taux[] = $data[$jour_en];
    }

    return [
        'jours' => $jours,
        'taux' => $taux
    ];
}


}
