<?php
class Cours
{
    private $id;
    private $titre;
    private $description;
    private $enseignant_id;
    public function __construct($titre, $description, $enseignant_id)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->enseignant_id = $enseignant_id;
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
    public static function ajouterCours($titre, $description, $level, $category, $image, $enseignant_id)
    {
        include("../Connexion/connexion.php");

        try {
            $req = $conn->prepare("
            INSERT INTO cours (titre, description, level, category, image, enseignant_id)
            VALUES (:titre, :description, :level, :category, :image, :enseignant_id)
        ");

            $req->bindParam(':titre', $titre);
            $req->bindParam(':description', $description);
            $req->bindParam(':level', $level);
            $req->bindParam(':category', $category);
            $req->bindParam(':image', $image);
            $req->bindParam(':enseignant_id', $enseignant_id);

            $req->execute();
            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public static function coursExiste($titre, $level, $excludeId = null)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            if ($excludeId) {
                // Vérification pour la modification (exclure un ID)
                $query = "SELECT COUNT(*) FROM cours WHERE titre = :titre AND level = :level AND id != :excludeId";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':titre', $titre);
                $stmt->bindParam(':level', $level);
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            } else {
                // Vérification pour l'ajout (vérification normale)
                $query = "SELECT COUNT(*) FROM cours WHERE titre = :titre AND level = :level";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':titre', $titre);
                $stmt->bindParam(':level', $level);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur dans coursExiste: " . $e->getMessage());
            return false;
        }
    }
    public static function countEtudiantsInscrits($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "SELECT COUNT(*) as nb_etudiants FROM inscription WHERE cours_id = :cours_id";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['nb_etudiants'];
        } catch (PDOException $e) {
            error_log("Erreur dans countEtudiantsInscrits: " . $e->getMessage());
            return 0;
        }
    }

    public static function getAllCoursWithDetails()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "
            SELECT 
                c.id,
                c.titre,
                c.description,
                c.level,
                c.category,
                c.image,
                c.enseignant_id,
                CONCAT(u.nom, ' ', u.prenom) as enseignant_nom,
                COUNT(i.etudiant_id) as nb_etudiants
            FROM cours c
            LEFT JOIN enseignant e ON c.enseignant_id = e.id
            LEFT JOIN utilisateur u ON e.id = u.id 
            LEFT JOIN inscriptionCours i ON c.id = i.cours_id
            GROUP BY c.id
            ORDER BY c.id
        ";

            $stmt = $conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getAllCoursWithDetails: " . $e->getMessage());
            return [];
        }
    }
    // Méthode pour mettre à jour un cours
    public static function updateCours($id, $titre, $description, $level, $category, $image, $enseignant_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "
            UPDATE cours 
            SET titre = :titre, description = :description, level = :level, category = :category, image = :image, enseignant_id = :enseignant_id
            WHERE id = :id
        ";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':level', $level);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':enseignant_id', $enseignant_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur dans updateCours: " . $e->getMessage());
            return false;
        }
    }
    public static function getAllCours()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT * FROM cours ORDER BY titre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getCoursByEnseignant($enseignant_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT * FROM cours  where enseignant_id=:enseignant_id ORDER BY titre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':enseignant_id', $enseignant_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getCoursPagines($limit, $offset)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT *
            FROM cours 
            ORDER BY titre ASC 
            LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countCours()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT COUNT(*) AS total FROM cours";
        return $conn->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }


    public static function getCoursById($id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "SELECT * FROM cours WHERE id = :id";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getCoursById: " . $e->getMessage());
            return null;
        }
    }
    public static function deleteCours($id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "DELETE FROM cours WHERE id = :id";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur dans deleteCours: " . $e->getMessage());
            return false;
        }
    }

    public static function hasSeancesAssociees($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        try {
            $query = "SELECT COUNT(*) as nb_seances FROM seance WHERE cours_id = :cours_id";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['nb_seances'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur dans hasSeancesAssociees: " . $e->getMessage());
            return false;
        }
    }
    public static  function getNombreCoursParEnseignant($enseignantId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $sql = "SELECT COUNT(*) as nb_cours 
            FROM cours 
            WHERE enseignant_id = :enseignant_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['enseignant_id' => $enseignantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nb_cours'];
    }
    public static function getCoursFiltresByEnseignant($enseignantId, $search = '', $level = 'all', $titre = '')
    {
        include(__DIR__ . '/../Connexion/connexion.php');

        $sql = "SELECT * FROM cours WHERE enseignant_id = ?";
        $params = [$enseignantId];


        if ($level !== 'all') {
            $sql .= " AND level = ?";
            $params[] = $level;
        }


        if (!empty($titre)) {
            $sql .= " AND titre = ?";
            $params[] = $titre;
        }

        if (!empty($search)) {
            $sql .= " AND (
            titre LIKE ? OR
            description LIKE ? OR
            level LIKE ? OR
            category LIKE ?
        )";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public static function getSeancesByCours($cours_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT s.id , s.date, s.cours_id, s.heureDebut,s.heureFin, titre FROM seance s join cours c on s.cours_id= c.id WHERE cours_id = :cours_id ORDER BY date ASC, heureDebut ASC");
        $stmt->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function tauxPresenceCours($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        try {
            // 1) Nombre total de séances du cours
            $query1 = "SELECT COUNT(*) AS nb_seances
                   FROM seance
                   WHERE cours_id = :cours_id";


            $stmt1 = $conn->prepare($query1);
            $stmt1->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt1->execute();
            $nbSeances = $stmt1->fetch(PDO::FETCH_ASSOC)['nb_seances'];


            if ($nbSeances == 0) return 0;


            // 2) Nombre total d'étudiants inscrits dans ce cours
            $query2 = "SELECT COUNT(*) AS nb_etudiants
                   FROM inscriptionCours
                   WHERE cours_id = :cours_id";


            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt2->execute();
            $nbEtudiants = $stmt2->fetch(PDO::FETCH_ASSOC)['nb_etudiants'];


            if ($nbEtudiants == 0) return 0;


            // 3) Nombre total de présences enregistrées (statut = 'present')
            $query3 = "SELECT COUNT(*) AS nb_presences
                   FROM presence p
                   JOIN seance s ON p.seance_id = s.id
                   WHERE s.cours_id = :cours_id
                   AND p.statut = 'present'";


            $stmt3 = $conn->prepare($query3);
            $stmt3->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt3->execute();
            $nbPresences = $stmt3->fetch(PDO::FETCH_ASSOC)['nb_presences'];


            // 4) Calcul du taux de présence
            $totalPossible = $nbEtudiants * $nbSeances;  // Présences possibles


            return round(($nbPresences / $totalPossible) * 100, 2);
        } catch (PDOException $e) {
            error_log("Erreur dans tauxPresenceCours: " . $e->getMessage());
            return 0;
        }
    }
    public static function getNbEtudiantsInscrits($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        try {
            $query = "SELECT COUNT(*) AS nb_etudiants
                  FROM inscriptionCours
                  WHERE cours_id = :cours_id";


            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt->execute();


            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['nb_etudiants'];
        } catch (PDOException $e) {
            error_log("Erreur dans getNbEtudiantsInscrits: " . $e->getMessage());
            return 0;
        }
    }
    public static function getNbSeances($coursId)
    {
        include(__DIR__ . "/../Connexion/connexion.php");


        try {
            $query = "SELECT COUNT(*) AS nb_seances FROM seance WHERE cours_id = :cours_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
            $stmt->execute();


            return $stmt->fetch(PDO::FETCH_ASSOC)['nb_seances'];
        } catch (PDOException $e) {
            error_log("Erreur dans getNbSeances: " . $e->getMessage());
            return 0;
        }
    }
    public static function getCoursFiltres($search = '', $category = 'all', $parPage = 8, $page = 1)
    {
        include(__DIR__ . '/../Connexion/connexion.php');

        $offset = ($page - 1) * $parPage;

        $sql = "SELECT * FROM cours WHERE 1";
        $params = [];

        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if (!empty($search)) {
            $sql .= " AND (titre LIKE ? OR description LIKE ? OR level LIKE ? OR category LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        // Limite et offset concaténés directement
        $parPage = (int)$parPage;
        $offset = (int)$offset;
        $sql .= " ORDER BY id DESC LIMIT $parPage OFFSET $offset";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countCoursFiltres($search = '', $category = 'all')
    {
        include(__DIR__ . '/../Connexion/connexion.php');

        $sql = "SELECT COUNT(*) as total FROM cours WHERE 1";
        $params = [];

        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if (!empty($search)) {
            $sql .= " AND (titre LIKE ? OR description LIKE ? OR level LIKE ? OR  category LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
