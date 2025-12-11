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
    public static function getHistoriquePresence()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $query = "
        SELECT s.date, s.heureDebut, s.heureFin, c.titre, 
               CONCAT(u.prenom, ' ', u.nom) AS nomEtudiant,  p.statut,p.seance_id,c.titre,s.date,s.heureDebut,s.heureFin
        FROM presence p
        JOIN seance s ON p.seance_id = s.id
            JOIN cours c ON c.id = s.cours_id 
            join utilisateur u on p.etudiant_id =u.id
        ORDER BY p.created_at DESC
    ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getTauxPresenceAnnuel()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $year = date('Y'); // année actuelle

        $sql = "SELECT COUNT(*) as totalCours, 
                   SUM(CASE WHEN statut = 'Présent' THEN 1 ELSE 0 END) as nbPresence
            FROM presence p
            JOIN seance s ON p.seance_id = s.id
              AND YEAR(s.date) = :year";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':year' => $year
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalCours = $result['totalCours'] ?? 0;
        $nbPresence = $result['nbPresence'] ?? 0;

        $tauxPresence = $totalCours > 0 ? round(($nbPresence / $totalCours) * 100) : 0;

        return $tauxPresence;
    }
    public static function getPresenceMois()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $year = date('Y');
        $month = date('m');

        $sql = "SELECT COUNT(*) as nbPresence
            FROM presence p
            JOIN seance s ON p.seance_id = s.id
              where p.statut = 'Présent'
              AND YEAR(s.date) = :year
              AND MONTH(s.date) = :month";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':month' => $month
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nbPresence'] ?? 0;
    }
    public static function getAbsence()
    {
        require(__DIR__ . "/../Connexion/connexion.php");

        $year = date('Y');

        $sql = "SELECT CONCAT(u.nom, ' ', u.prenom) AS etudiant,
                   COUNT(p.id) AS nbAbsence,
                   c.titre AS nomCours
            FROM presence p
            JOIN seance s ON p.seance_id = s.id
            JOIN cours c ON s.cours_id = c.id
            JOIN utilisateur u ON u.id = p.etudiant_id
            WHERE p.statut = 'Absent'
              AND YEAR(s.date) = :year
            GROUP BY p.etudiant_id, c.id
            HAVING nbAbsence >= 3
            ORDER BY nbAbsence DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':year' => $year
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
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
    public static function getWeeklyAbsence()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT COUNT(*) AS nbAbsence
            FROM presence p
            JOIN seance s ON p.seance_id = s.id
            WHERE p.statut = 'Absent'
              AND YEARWEEK(s.date, 1) = YEARWEEK(CURDATE(), 1)";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['nbAbsence'] ?? 0;
    }

    public static function getWeeklyPresenceRate()
    {
        include(__DIR__ . "/../Connexion/connexion.php");


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
        $jours_semaine = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        $data = array_fill_keys($jours_semaine, 0);

        foreach ($rows as $row) {
            $jour_nom = $row['jour_nom'];
            $rate = $row['total'] > 0 ? round(($row['presents'] / $row['total']) * 100, 2) : 0;
            $data[$jour_nom] = $rate;
        }
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
    public static function tauxPresenceParCours()
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        $sql = "SELECT c.titre AS cours, 
                   ROUND(SUM(CASE WHEN p.statut = 'Présent' THEN 1 ELSE 0 END) / COUNT(p.id) * 100, 2) AS tauxPresence
            FROM presence p
            JOIN seance s ON p.seance_id = s.id
            JOIN cours c ON s.cours_id = c.id
            GROUP BY c.id
            ORDER BY c.titre ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getStatsBySeance($seance_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "
        SELECT 
            SUM(CASE WHEN statut = 'Présent' THEN 1 ELSE 0 END) AS nbPresent,
            SUM(CASE WHEN statut = 'Absent' THEN 1 ELSE 0 END) AS nbAbsent
   
        FROM presence
        WHERE seance_id = :seance_id
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':seance_id' => $seance_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'nbPresent' => $result['nbPresent'] ?? 0,
            'nbAbsent' => $result['nbAbsent'] ?? 0
            // 'nbEnAttente' => $result['nbEnAttente'] ?? 0
        ];
    }
    public static function getStatutByEtudiantSeance(int $etudiant_id, int $seance_id): string
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT statut 
            FROM presence 
            WHERE etudiant_id = :etudiant_id 
              AND seance_id = :seance_id
            LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id
        ]);

        $statut = $stmt->fetchColumn();

        return $statut ?: 'EnAttente';
    }
    public static function setPresent($etudiant_id, $seance_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "UPDATE presence 
            SET statut = 'Présent'
            WHERE etudiant_id = :etudiant_id
              AND seance_id = :seance_id";

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id
        ]);
    }
    public static function setAbsent($etudiant_id, $seance_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "UPDATE presence 
            SET statut = 'Absent'
            WHERE etudiant_id = :etudiant_id
              AND seance_id = :seance_id";

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id
        ]);
    }
    public static function checkAbsences()
    {
        require(__DIR__ . "/../Connexion/connexion.php");

        $year = date('Y');

        $sql = "SELECT p.etudiant_id, CONCAT(u.nom,' ',u.prenom) AS etudiant,
                       COUNT(p.id) AS nbAbsence,
                       c.id AS cours_id, c.titre AS nomCours,
                       c.enseignant_id
                FROM presence p
                JOIN seance s ON p.seance_id = s.id
                JOIN cours c ON s.cours_id = c.id
                JOIN utilisateur u ON u.id = p.etudiant_id
                WHERE p.statut = 'Absent'
                  AND YEAR(s.date) = :year
                GROUP BY p.etudiant_id, c.id, c.enseignant_id
                HAVING nbAbsence >= 2";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':year' => $year]);
        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $absences;
    }
    public static function getPresencesBySeance($seanceId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        try {
            $sql = "
             SELECT
                p.id,
                p.etudiant_id,
                p.seance_id,
                p.statut,
                u.photoProfil,
                u.email,
                p.created_at as HeureArrivee,
                u.nom AS etudiant_nom,
                u.prenom AS etudiant_prenom,
                e.matricule AS etudiant_matricule
            FROM presence p
            INNER JOIN utilisateur u ON p.etudiant_id = u.id
            INNER JOIN etudiant e ON p.etudiant_id = e.id
            WHERE p.seance_id = :seanceId
        ";


            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':seanceId', $seanceId, PDO::PARAM_INT);
            $stmt->execute();


            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur getPresencesBySeance : " . $e->getMessage());
        }
    }
}
