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
    public static function findSeanceByEnseignant($utilisateur_id, $date)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT  s.id,        
   s.date,
    s.heureDebut,
    s.heureFin,
    s.cours_id,c.titre,c.description,c.enseignant_id
            FROM seance s
          
            JOIN cours c ON c.id = s.cours_id 
            WHERE c.enseignant_id = :id 
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
    public static function findAllSeances()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $query = "SELECT s.id, s.date, s.heureDebut, s.heureFin FROM seance s ";

        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getSeancesStatistics()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        date_default_timezone_set('Africa/Tunis');
        $seances = self::findAllSeances();
        $now = new DateTime();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        $active = 0;
        $completed = 0;
        $upcoming = 0;
        foreach ($seances as $seance) {
            $seanceDate = $seance['date'];
            $heureDebut = $seance['heureDebut'];
            $heureFin = $seance['heureFin'];
            if (
                $seanceDate == $today &&
                $currentTime >= $heureDebut &&
                $currentTime <= $heureFin
            ) {
                $active++;
            } elseif (
                $seanceDate < $today ||
                ($seanceDate == $today && $currentTime > $heureFin)
            ) {
                $completed++;
            } elseif (
                $seanceDate > $today ||
                ($seanceDate == $today && $currentTime < $heureDebut)
            ) {
                $upcoming++;
            }
        }

        return [
            'active' => $active,
            'completed' => $completed,
            'upcoming' => $upcoming,
            'total' => count($seances)
        ];
    }
    public static function findSeanceMoisByEnseignant($utilisateur_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $month = date('m');
        $sql = " SELECT 
            s.id,
            s.date,
            s.heureDebut,
            s.heureFin,
            s.cours_id,
            c.titre,
            c.description,
            c.enseignant_id,

            -- Nombre total d'étudiants inscrits dans le cours
            (SELECT COUNT(*) 
             FROM inscriptioncours ic 
             WHERE ic.cours_id = s.cours_id) AS nb_total,

            -- Nombre de présences dans cette séance
            (SELECT COUNT(*) 
             FROM presence p 
             WHERE p.seance_id = s.id 
               AND p.statut = 'Présent') AS nb_present

        FROM seance s
        JOIN cours c ON c.id = s.cours_id
        WHERE c.enseignant_id = :id
          AND MONTH(s.date) = :month
        ORDER BY s.date DESC
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $utilisateur_id,
            ':month' => $month
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getSeancesByCoursEtEnseignant($idCours, $idEnseignant)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("
        SELECT s.* 
        FROM seance s
        JOIN cours c ON c.id = s.cours_id
        WHERE c.enseignant_id = :idEnseignant 
          AND s.cours_id = :idCours
        ORDER BY s.date ASC
    ");
        $stmt->execute([
            'idCours' => $idCours,
            'idEnseignant' => $idEnseignant
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getSeancesWithStatsByCours($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT 
                s.id,
                s.date,
                s.heureDebut,
                s.heureFin,
                s.cours_id,
                c.titre,
                c.description,
                c.enseignant_id,
                (SELECT COUNT(*) FROM inscriptioncours ic WHERE ic.cours_id = s.cours_id) AS nb_total,
                (SELECT COUNT(*) FROM presence p WHERE p.seance_id = s.id AND p.statut = 'Présent') AS nb_present
            FROM seance s
            JOIN cours c ON c.id = s.cours_id
            WHERE s.cours_id = :coursId
            ORDER BY s.date ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':coursId' => $coursId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getSeanceById($idSeance)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT * FROM seance WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $idSeance]);

        $seance = $stmt->fetch(PDO::FETCH_ASSOC);
        return $seance ?: null;
    }


    public static function isTimeSlotAvailable($cours_id, $date, $heureDebut, $heureFin, $seance_id = null)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        $sql = "
        SELECT COUNT(*)
        FROM seance
        WHERE cours_id = :cours_id
          AND date = :date
          AND (heureDebut < :heureFin AND heureFin > :heureDebut)
    ";


        // Exclure la séance actuelle en cas de modification
        if ($seance_id !== null) {
            $sql .= " AND id != :seance_id";
        }


        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':heureDebut', $heureDebut);
        $stmt->bindParam(':heureFin', $heureFin);


        if ($seance_id !== null) {
            $stmt->bindParam(':seance_id', $seance_id, PDO::PARAM_INT);
        }


        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }




    // Ajoute une séance
    public static function addSeance($cours_id, $date, $heureDebut, $heureFin)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        // Vérification du créneau
        if (!self::isTimeSlotAvailable($cours_id, $date, $heureDebut, $heureFin)) {
            return false; // créneau déjà pris
        }
        $stmt = $conn->prepare(" INSERT INTO seance (cours_id, date, heureDebut, heureFin) VALUES (:cours_id, :date, :heureDebut, :heureFin) ");
        $stmt->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':heureDebut', $heureDebut);
        $stmt->bindParam(':heureFin', $heureFin);
        return $stmt->execute(); // true si insertion réussie
    }
    public static function updateSeance($seance_id, $cours_id, $date, $heureDebut, $heureFin)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        // Vérifier si le créneau est disponible (sauf cette séance)
        if (!self::isTimeSlotAvailable($cours_id, $date, $heureDebut, $heureFin, $seance_id)) {
            return false; // conflit avec une autre séance
        }


        $stmt = $conn->prepare("
        UPDATE seance
        SET date = :date,
            heureDebut = :heureDebut,
            heureFin = :heureFin
        WHERE id = :seance_id
    ");


        $stmt->bindParam(':seance_id', $seance_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':heureDebut', $heureDebut);
        $stmt->bindParam(':heureFin', $heureFin);


        return $stmt->execute();
    }
    public static function deleteSeance($seance_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        try {
            // Suppression de la séance
            $stmt = $conn->prepare("DELETE FROM seance WHERE id = :id");
            $stmt->bindParam(':id', $seance_id, PDO::PARAM_INT);


            return $stmt->execute(); // true si suppression ok
        } catch (PDOException $e) {
            return false;
        }
    }
}
