<?php
class Cours {
    private $id;
    private $titre;
    private $description;
    private $enseignant_id;

    public function __construct($id = null, $titre = null, $description = null, $enseignant_id = null) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->enseignant_id = $enseignant_id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getEnseignantId() {
        return $this->enseignant_id;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setEnseignantId($enseignant_id) {
        $this->enseignant_id = $enseignant_id;
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
    public static function getAllCoursWithDetails() {
        include(__DIR__ . "/../Connexion/connexion.php");
        
        try {
                   $query = "
            SELECT 
                c.id,
                c.titre,
                c.description,
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

    public static function getCoursById($id) {
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



    // Méthode pour mettre à jour un cours
    public static function updateCours($id, $titre, $description, $enseignant_id) {
        include(__DIR__ . "/../Connexion/connexion.php");
        
        try {
            $query = "UPDATE cours SET titre = :titre, description = :description, enseignant_id = :enseignant_id WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':enseignant_id', $enseignant_id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erreur dans updateCours: " . $e->getMessage());
            return false;
        }
    }

    // Méthode pour supprimer un cours
    public static function deleteCours($id) {
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

    // Méthode pour vérifier si un cours a des séances associées
    public static function hasSeancesAssociees($coursId) {
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

    // Méthode pour compter le nombre d'étudiants inscrits à un cours
    public static function countEtudiantsInscrits($coursId) {
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

    // Méthode pour récupérer les étudiants inscrits à un cours
    // public static function getEtudiantsInscrits($coursId) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "
    //             SELECT 
    //                 e.id,
    //                 e.nom,
    //                 e.prenom,
    //                 e.email
    //             FROM etudiant e
    //             INNER JOIN inscription i ON e.id = i.etudiant_id
    //             WHERE i.cours_id = :cours_id
    //             ORDER BY e.nom, e.prenom
    //         ";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
    //         $stmt->execute();
            
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans getEtudiantsInscrits: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // Méthode pour récupérer les séances d'un cours
    // public static function getSeancesByCours($coursId) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "
    //             SELECT 
    //                 s.id,
    //                 s.date,
    //                 s.heureDebut,
    //                 s.heureFin
    //             FROM seance s
    //             WHERE s.cours_id = :cours_id
    //             ORDER BY s.date, s.heureDebut
    //         ";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
    //         $stmt->execute();
            
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans getSeancesByCours: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // Méthode pour vérifier si un titre de cours existe déjà
    // public static function titreExists($titre, $excludeId = null) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         if ($excludeId) {
    //             $query = "SELECT COUNT(*) as count FROM cours WHERE titre = :titre AND id != :exclude_id";
    //             $stmt = $conn->prepare($query);
    //             $stmt->bindParam(':titre', $titre);
    //             $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
    //         } else {
    //             $query = "SELECT COUNT(*) as count FROM cours WHERE titre = :titre";
    //             $stmt = $conn->prepare($query);
    //             $stmt->bindParam(':titre', $titre);
    //         }
            
    //         $stmt->execute();
    //         $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //         return $result['count'] > 0;
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans titreExists: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // Méthode pour récupérer les cours par enseignant
    // public static function getCoursByEnseignant($enseignantId) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "
    //             SELECT 
    //                 c.id,
    //                 c.titre,
    //                 c.description,
    //                 COUNT(i.etudiant_id) as nb_etudiants
    //             FROM cours c
    //             LEFT JOIN inscription i ON c.id = i.cours_id
    //             WHERE c.enseignant_id = :enseignant_id
    //             GROUP BY c.id
    //             ORDER BY c.titre
    //         ";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->bindParam(':enseignant_id', $enseignantId, PDO::PARAM_INT);
    //         $stmt->execute();
            
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans getCoursByEnseignant: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // Méthode pour récupérer le nombre total de cours
    // public static function getTotalCours() {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "SELECT COUNT(*) as total FROM cours";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->execute();
            
    //         $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //         return $result['total'];
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans getTotalCours: " . $e->getMessage());
    //         return 0;
    //     }
    // }

    // Méthode pour rechercher des cours
    // public static function searchCours($searchTerm) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "
    //             SELECT 
    //                 c.id,
    //                 c.titre,
    //                 c.description,
    //                 CONCAT(e.nom, ' ', e.prenom) as enseignant_nom,
    //                 COUNT(i.etudiant_id) as nb_etudiants
    //             FROM cours c
    //             LEFT JOIN enseignant e ON c.enseignant_id = e.id
    //             LEFT JOIN inscription i ON c.id = i.cours_id
    //             WHERE c.titre LIKE :search OR c.description LIKE :search OR e.nom LIKE :search OR e.prenom LIKE :search
    //             GROUP BY c.id
    //             ORDER BY c.titre
    //         ";
            
    //         $stmt = $conn->prepare($query);
    //         $searchTerm = "%" . $searchTerm . "%";
    //         $stmt->bindParam(':search', $searchTerm);
    //         $stmt->execute();
            
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans searchCours: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // Méthode pour inscrire un étudiant à un cours
    // public static function inscrireEtudiant($coursId, $etudiantId) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         // Vérifier si l'étudiant est déjà inscrit
    //         $checkQuery = "SELECT COUNT(*) as count FROM inscription WHERE cours_id = :cours_id AND etudiant_id = :etudiant_id";
    //         $checkStmt = $conn->prepare($checkQuery);
    //         $checkStmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
    //         $checkStmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
    //         $checkStmt->execute();
    //         $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
    //         if ($result['count'] > 0) {
    //             return false; // Déjà inscrit
    //         }
            
    //         $query = "INSERT INTO inscription (cours_id, etudiant_id) VALUES (:cours_id, :etudiant_id)";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
    //         $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            
    //         return $stmt->execute();
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans inscrireEtudiant: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // Méthode pour désinscrire un étudiant d'un cours
    // public static function desinscrireEtudiant($coursId, $etudiantId) {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "DELETE FROM inscription WHERE cours_id = :cours_id AND etudiant_id = :etudiant_id";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->bindParam(':cours_id', $coursId, PDO::PARAM_INT);
    //         $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            
    //         return $stmt->execute();
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans desinscrireEtudiant: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // Méthode pour récupérer les statistiques des cours
    // public static function getCoursStatistics() {
    //     include(__DIR__ . "/../Connexion/connexion.php");
        
    //     try {
    //         $query = "
    //             SELECT 
    //                 COUNT(DISTINCT c.id) as total_cours,
    //                 COUNT(DISTINCT e.id) as total_enseignants,
    //                 COUNT(DISTINCT i.etudiant_id) as total_etudiants_inscrits,
    //                 AVG(etudiants_par_cours.nb_etudiants) as moyenne_etudiants_par_cours
    //             FROM cours c
    //             LEFT JOIN enseignant e ON c.enseignant_id = e.id
    //             LEFT JOIN inscription i ON c.id = i.cours_id
    //             LEFT JOIN (
    //                 SELECT cours_id, COUNT(etudiant_id) as nb_etudiants 
    //                 FROM inscription 
    //                 GROUP BY cours_id
    //             ) etudiants_par_cours ON c.id = etudiants_par_cours.cours_id
    //         ";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->execute();
            
    //         return $stmt->fetch(PDO::FETCH_ASSOC);
            
    //     } catch (PDOException $e) {
    //         error_log("Erreur dans getCoursStatistics: " . $e->getMessage());
    //         return [
    //             'total_cours' => 0,
    //             'total_enseignants' => 0,
    //             'total_etudiants_inscrits' => 0,
    //             'moyenne_etudiants_par_cours' => 0
    //         ];
    //     }
    // }
}
?>