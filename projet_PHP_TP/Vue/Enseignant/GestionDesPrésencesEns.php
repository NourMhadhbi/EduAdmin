<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'enseignant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - Espace Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .attendance-table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }
        
        .student-col {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
        }
        
        .attendance-status {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin: 0 auto;
        }
        
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .late {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .session-header {
            background-color: #e9ecef;
            font-weight: 600;
        }
        
        .attendance-summary {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
        <?php include("../NavBar/navbar.php") ?>
    <!-- Utiliser la même navbar et sidebar que le tableau de bord -->
    
   <main class="px-md-4 py-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
            <h1 class="h2">Gestion des présences</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-success me-2">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-file-export me-1"></i> Exporter
                </button>
            </div>
        </div>

        <!-- Sélecteur de cours -->
        <div class="attendance-summary">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="courseSelect" class="form-label">Sélectionnez un cours</label>
                    <select class="form-select" id="courseSelect">
                        <option selected>Développement Web Avancé</option>
                        <option>Base de Données</option>
                        <option>Algorithmique</option>
                        <option>Intelligence Artificielle</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sessionSelect" class="form-label">Sélectionnez une séance</label>
                    <select class="form-select" id="sessionSelect">
                        <option selected>Séance du 10 Octobre 2025</option>
                        <option>Séance du 03 Octobre 2025</option>
                        <option>Séance du 26 Septembre 2025</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <div class="h4 mb-0 text-success">32</div>
                            <small>Présents</small>
                        </div>
                        <div>
                            <div class="h4 mb-0 text-danger">3</div>
                            <small>Absents</small>
                        </div>
                        <div>
                            <div class="h4 mb-0 text-warning">2</div>
                            <small>Retards</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des présences -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Liste des présences - Développement Web Avancé</h5>
                <div>
                    <span class="badge bg-success me-1">Présent</span>
                    <span class="badge bg-danger me-1">Absent</span>
                    <span class="badge bg-warning me-1">En retard</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                    <table class="table table-bordered table-hover attendance-table">
                        <thead>
                            <tr>
                                <th class="student-col">Étudiant</th>
                                <th class="session-header text-center">10/10</th>
                                <th class="session-header text-center">03/10</th>
                                <th class="session-header text-center">26/09</th>
                                <th class="session-header text-center">19/09</th>
                                <th class="session-header text-center">12/09</th>
                                <th class="session-header text-center">05/09</th>
                                <th class="session-header text-center">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="student-col">Thomas Leroy</td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status absent" title="Absent">A</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status late" title="En retard">R</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><span class="badge bg-success">83%</span></td>
                            </tr>
                            <tr>
                                <td class="student-col">Marie Petit</td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><span class="badge bg-success">100%</span></td>
                            </tr>
                            <tr>
                                <td class="student-col">Jean Moreau</td>
                                <td class="text-center"><div class="attendance-status absent" title="Absent">A</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status absent" title="Absent">A</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><div class="attendance-status absent" title="Absent">A</div></td>
                                <td class="text-center"><div class="attendance-status present" title="Présent">P</div></td>
                                <td class="text-center"><span class="badge bg-warning">50%</span></td>
                            </tr>
                            <!-- Plus d'étudiants... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions de groupe -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions de groupe</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="groupAction" class="form-label">Action</label>
                        <select class="form-select" id="groupAction">
                            <option selected disabled>Sélectionnez une action</option>
                            <option>Marquer tous comme présents</option>
                            <option>Marquer tous comme absents</option>
                            <option>Marquer les sélectionnés comme présents</option>
                            <option>Marquer les sélectionnés comme absents</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="sessionDate" class="form-label">Pour la séance du</label>
                        <input type="date" class="form-control" id="sessionDate">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100">
                            <i class="fas fa-play me-1"></i> Exécuter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
   <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour changer le statut de présence au clic
        document.addEventListener('DOMContentLoaded', function() {
            const statusElements = document.querySelectorAll('.attendance-status');
            
            statusElements.forEach(element => {
                element.addEventListener('click', function() {
                    if (this.classList.contains('present')) {
                        this.classList.remove('present');
                        this.classList.add('absent');
                        this.textContent = 'A';
                        this.title = 'Absent';
                    } else if (this.classList.contains('absent')) {
                        this.classList.remove('absent');
                        this.classList.add('late');
                        this.textContent = 'R';
                        this.title = 'En retard';
                    } else {
                        this.classList.remove('late');
                        this.classList.add('present');
                        this.textContent = 'P';
                        this.title = 'Présent';
                    }
                });
            });
        });
    </script>
</body>
</html>