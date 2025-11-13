<?php
// require_once("../Connexion/connexion.php");

class Utilisateur
{
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $motDePasse;
    private $role;
    private $photoProfil;

    public function __construct($nom, $prenom, $email, $motDePasse, $role, $photoProfil = "default_profile.png")
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->role = $role;
        $this->photoProfil = $photoProfil;
    }

    // --- Getters et setters magiques ---
    public function __get($attr)
    {
        return $this->$attr ?? null;
    }

    public function __set($attr, $value)
    {
        $this->$attr = $value;
    }
    public static function Connection($email, $motDePass)
    {
        include("../Connexion/connexion.php");

        // Vérifier si l'email existe
        $req = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $req->bindParam(':email', $email);
        $req->execute();

        if ($req->rowCount() == 0) {
            // Aucun compte trouvé avec cet email
            return ['error' => 'email_not_found'];
        }

        // Si l'email existe, on vérifie le mot de passe
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($motDePass, $user['motDePasse'])) {
            return ['error' => 'wrong_password'];
        }

        // Si tout est bon
        return $user;
    }

    public function ajouterCompte($matricule = null, $specialite = null)
    {
        include("../Connexion/connexion.php");
        try {
            $conn->beginTransaction();
            $checkEmail = $conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
            $checkEmail->execute([$this->email]);
            if ($checkEmail->fetchColumn() > 0) {
                throw new Exception("Cet email est déjà utilisé.");
            }
            if ($matricule) {
                $checkMatriculeEtudiant = $conn->prepare("SELECT COUNT(*) FROM etudiant WHERE matricule = ?");
                $checkMatriculeEtudiant->execute([$matricule]);
                $checkMatriculeEnseignant = $conn->prepare("SELECT COUNT(*) FROM enseignant WHERE matricule = ?");
                $checkMatriculeEnseignant->execute([$matricule]);

                if ($checkMatriculeEtudiant->fetchColumn() > 0 || $checkMatriculeEnseignant->fetchColumn() > 0) {
                    throw new Exception("Ce matricule existe déjà.");
                }
            }
            // Hachage du mot de passe
            $motDePasseHache = password_hash($this->motDePasse, PASSWORD_DEFAULT);
            // Insertion dans utilisateur
            $stmtUser = $conn->prepare("
                INSERT INTO utilisateur (nom, prenom, email, motDePasse, role, photoProfil)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtUser->execute([
                $this->nom,
                $this->prenom,
                $this->email,
                $motDePasseHache,
                $this->role,
                $this->photoProfil
            ]);

            $userId = $conn->lastInsertId();
            if ($this->role === 'Etudiant') {
                $stmtEtudiant = $conn->prepare("INSERT INTO etudiant (id, matricule) VALUES (?, ?)");
                $stmtEtudiant->execute([$userId, $matricule]);
            } elseif ($this->role === 'Enseignant') {
                $stmtEns = $conn->prepare("INSERT INTO enseignant (id, matricule, specialite) VALUES (?, ?, ?)");
                $stmtEns->execute([$userId, $matricule, $specialite]);
            }

            $conn->commit();
            return [
                'success' => true,
                'id' => $userId
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erreur lors de l'ajout du compte : " . $e->getMessage());
            return $e->getMessage();
        }
    }
    public static function getStatsByAdmin()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT role, COUNT(id) AS total 
            FROM utilisateur 
            WHERE role IN ('Etudiant', 'Enseignant', 'Admin')
            GROUP BY role";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'totalEtudiant' => 0,
            'totalEnseignant' => 0,
            'totalAdmin' => 0
        ];

        foreach ($results as $row) {
            switch ($row['role']) {
                case 'Etudiant':
                    $stats['totalEtudiant'] = (int)$row['total'];
                    break;
                case 'Enseignant':
                    $stats['totalEnseignant'] = (int)$row['total'];
                    break;
                case 'Admin':
                    $stats['totalAdmin'] = (int)$row['total'];
                    break;
            }
        }

        return $stats;
    }
    public static function getEnAttenteCount()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sqlEtudiant = "SELECT COUNT(id) AS total FROM etudiant WHERE isActive = 0";
        $stmt = $conn->prepare($sqlEtudiant);
        $stmt->execute();
        $totalEtudiant = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $sqlEnseignant = "SELECT COUNT(id) AS total FROM enseignant WHERE isActive = 0";
        $stmt = $conn->prepare($sqlEnseignant);
        $stmt->execute();
        $totalEnseignant = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $totalEnAttente = $totalEtudiant + $totalEnseignant;

        return $totalEnAttente;
    }

    public static function getAllUsers()
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "
        SELECT u.id, u.nom, u.prenom, u.email, u.role,
               e.matricule AS etuMatricule, t.matricule AS ensMatricule,
               e.isActive AS etuActive, t.isActive AS ensActive,
               t.specialite
        FROM utilisateur u
        LEFT JOIN etudiant e ON u.id = e.id
        LEFT JOIN enseignant t ON u.id = t.id
        ORDER BY u.role, u.nom
    ";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }
    public static function getEtudiantsFiltrés($search = '', $status = 'all')
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT u.id, u.nom, u.prenom, u.email, u.role, e.matricule, e.isActive
                FROM utilisateur u
                JOIN etudiant e ON u.id = e.id
                WHERE 1";
        $params = [];

        if ($status === 'Actif') {
            $sql .= " AND e.isActive = 1";
        } elseif ($status === 'Inactif') {
            $sql .= " AND e.isActive = 0";
        }

        if (!empty($search)) {
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEnseignantsFiltrés($search = '', $status = 'all')
    {
        include(__DIR__ . "/../Connexion/connexion.php");

        $sql = "SELECT u.id, u.nom, u.prenom, u.email, u.role, t.matricule, t.specialite, t.isActive
                FROM utilisateur u
                JOIN enseignant t ON u.id = t.id
                WHERE 1";
        $params = [];
        if ($status === 'Actif') {
            $sql .= " AND t.isActive = 1";
        } elseif ($status === 'Inactif') {
            $sql .= " AND t.isActive = 0";
        }
        if (!empty($search)) {
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getById($id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function updateIsActive($id, $status)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("UPDATE utilisateur u
                                LEFT JOIN etudiant e ON u.id = e.id
                                LEFT JOIN enseignant en ON u.id = en.id
                                SET e.isActive = :status, en.isActive = :status
                                WHERE u.id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
    public static function getUserByEmail($email)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function saveResetCode($email, $code)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("UPDATE utilisateur SET reset_code = ?, reset_expire = NOW() + INTERVAL 10 MINUTE WHERE email = ?");
        $stmt->execute([$code, $email]);
    }

    public static   function verifyResetCode($email, $code)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ? AND reset_code = ? AND reset_expire > NOW()");
        $stmt->execute([$email, $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static   function updateUserPassword($email, $newPassword)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $stmt = $conn->prepare("UPDATE utilisateur SET motDePasse = ?, reset_code = NULL, reset_expire = NULL WHERE email = ?");
        $stmt->execute([$newPassword, $email]);
    }
}
