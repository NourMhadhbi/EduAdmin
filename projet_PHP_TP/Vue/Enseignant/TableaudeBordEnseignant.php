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
    <title>Système de Présence - Espace Enseignant</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #1abc9c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
        }

        .navbar-brand {
            font-weight: 700;
        }

        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            min-height: calc(100vh - 56px);
            padding: 0;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .table th {
            background-color: var(--light-color);
            border-top: none;
        }

        .badge-present {
            background-color: #28a745;
        }

        .badge-absent {
            background-color: #dc3545;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .course-card {
            border-left: 4px solid var(--primary-color);
        }

        .progress {
            height: 8px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .page-header {
            padding-bottom: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style> -->
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #1abc9c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
        }

        .navbar-brand {
            font-weight: 700;
        }

        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            min-height: calc(100vh - 56px);
            padding: 0;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .table th {
            background-color: var(--light-color);
            border-top: none;
        }

        .badge-present {
            background-color: #28a745;
        }

        .badge-absent {
            background-color: #dc3545;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .course-card {
            border-left: 4px solid var(--primary-color);
        }

        .progress {
            height: 8px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .page-header {
            padding-bottom: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        /* Styles pour le modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: var(--dark-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation principale -->
    <?php include("../NavBar/navbar.php") ?>

    <div class="container-fluid">
        <div class="row">


            <!-- Contenu principal -->
            <main class="px-md-4 py-4">
                <!-- En-tête de page -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
                    <h1 class="h2">Tableau de bord</h1> <?php echo "iddd" . $_SESSION['id'] ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar me-1"></i> Semaine
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar me-1"></i> Mois
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="openAddCourseModal">
                            <i class="fas fa-plus me-1"></i> Nouveau cours
                        </button>
                    </div>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-users"></i>
                                <div class="number">142</div>
                                <div class="label">Étudiants inscrits</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-book-open"></i>
                                <div class="number">8</div>
                                <div class="label">Cours actifs</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-calendar-check"></i>
                                <div class="number">87%</div>
                                <div class="label">Taux de présence</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-exclamation-circle"></i>
                                <div class="number">12</div>
                                <div class="label">Absences cette semaine</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Cours récents -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Cours récents</h5>
                                <a href="#" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom du cours</th>
                                                <th>Date</th>
                                                <th>Heure</th>
                                                <th>Présents</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Développement Web Avancé</td>
                                                <td>10 Oct. 2025</td>
                                                <td>14:00 - 16:00</td>
                                                <td>32/35</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Base de Données</td>
                                                <td>08 Oct. 2025</td>
                                                <td>10:00 - 12:00</td>
                                                <td>28/35</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Algorithmique</td>
                                                <td>06 Oct. 2025</td>
                                                <td>09:00 - 11:00</td>
                                                <td>30/35</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Intelligence Artificielle</td>
                                                <td>11 Oct. 2025</td>
                                                <td>16:00 - 18:00</td>
                                                <td>-</td>
                                                <td><span class="badge bg-warning text-dark">À venir</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques de présence -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Statistiques de présence par cours</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panneau latéral -->
                    <div class="col-lg-4">
                        <!-- Prochains cours -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Prochains cours</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <div>
                                            <h6 class="mb-1">Intelligence Artificielle</h6>
                                            <small class="text-muted">11 Oct. 2025, 16:00 - 18:00</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">35</span>
                                    </div>
                                    <div
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <div>
                                            <h6 class="mb-1">Développement Web Avancé</h6>
                                            <small class="text-muted">13 Oct. 2025, 14:00 - 16:00</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">35</span>
                                    </div>
                                    <div
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <div>
                                            <h6 class="mb-1">Base de Données</h6>
                                            <small class="text-muted">15 Oct. 2025, 10:00 - 12:00</small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">35</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étudiants avec absences répétées -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Alertes d'absence</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Thomas Leroy</h6>
                                            <small class="text-danger">3 absences</small>
                                        </div>
                                        <p class="mb-1">Développement Web Avancé</p>
                                    </div>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Marie Petit</h6>
                                            <small class="text-danger">2 absences</small>
                                        </div>
                                        <p class="mb-1">Base de Données</p>
                                    </div>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Jean Moreau</h6>
                                            <small class="text-warning">1 absence</small>
                                        </div>
                                        <p class="mb-1">Algorithmique</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions rapides -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Créer un nouveau cours
                                    </button>
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-file-export me-2"></i> Exporter les présences
                                    </button>
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-chart-bar me-2"></i> Voir les statistiques
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <!-- Modal pour Ajouter un Cours -->
        <div class="modal" id="addCourseModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Ajouter un Nouveau Cours</h3>
                    <button class="modal-close" data-modal="addCourseModal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="addCourseForm" action="../../Controller/EnseignantController.php" method="POST">
                        <input type="hidden" name="action" value="ajouterCours">

                        <div class="form-group">
                            <label class="form-label" for="courseTitle">Titre du Cours</label>
                            <input type="text" class="form-control" id="courseTitle" name="titre"
                                placeholder="Ex: Algorithmique Avancée" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="courseTeacher">Enseignant</label>
                            <select class="form-control" id="courseTeacher" name="enseignant_id" required>
                                <option value="">Sélectionner un enseignant</option>
                                <!-- Les options des enseignants seront chargées dynamiquement -->
                                <option value="<?php echo $_SESSION['id'] ?? ''; ?>" selected>
                                    <?php echo $prenom . ' ' . $nom; ?>
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="courseDescription">Description du Cours</label>
                            <textarea class="form-control" id="courseDescription" name="description" rows="3"
                                placeholder="Description du cours..."></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-modal="addCourseModal">Annuler</button>
                            <button type="submit" class="btn btn-primary" id="saveCourseBtn">Enregistrer le Cours</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include("../../Footer/footer.php") ?>
    <!-- Bootstrap & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script>
        // Initialisation du graphique de présence
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Dev Web Avancé', 'Base de Données', 'Algorithmique', 'IA', 'Réseaux', 'Sécurité'],
                    datasets: [{
                        label: 'Taux de présence (%)',
                        data: [92, 80, 86, 78, 85, 90],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(230, 126, 34, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgb(52, 152, 219)',
                            'rgb(46, 204, 113)',
                            'rgb(155, 89, 182)',
                            'rgb(241, 196, 15)',
                            'rgb(230, 126, 34)',
                            'rgb(231, 76, 60)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script> -->
    <script>
        // Initialisation du graphique de présence
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Dev Web Avancé', 'Base de Données', 'Algorithmique', 'IA', 'Réseaux', 'Sécurité'],
                    datasets: [{
                        label: 'Taux de présence (%)',
                        data: [92, 80, 86, 78, 85, 90],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(241, 196, 15, 0.7)',
                            'rgba(230, 126, 34, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgb(52, 152, 219)',
                            'rgb(46, 204, 113)',
                            'rgb(155, 89, 182)',
                            'rgb(241, 196, 15)',
                            'rgb(230, 126, 34)',
                            'rgb(231, 76, 60)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Gestion du modal pour ajouter un cours
            const modal = document.getElementById('addCourseModal');
            const openButtons = document.querySelectorAll('#openAddCourseModal, #openAddCourseModal2');
            const closeButtons = document.querySelectorAll('.modal-close, [data-modal="addCourseModal"]');

            // Ouvrir le modal
            openButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Empêcher le défilement
                });
            });

            // Fermer le modal
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; // Rétablir le défilement
                });
            });

            // Fermer le modal en cliquant à l'extérieur
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });

            // Gérer la soumission du formulaire
            //document.getElementById('addCourseForm').addEventListener('submit', function(e) {
            // e.preventDefault();

            // Ici, vous pouvez ajouter votre logique pour sauvegarder le cours
            // Par exemple, envoyer les données via AJAX

            // Pour l'instant, on simule juste un enregistrement
            //const courseTitle = document.getElementById('courseTitle').value;
            //alert(`Cours "${courseTitle}" créé avec succès!`);

            // Fermer le modal
            //modal.style.display = 'none';
            //document.body.style.overflow = 'auto';

            // Réinitialiser le formulaire
            //this.reset();
            // });
            document.getElementById('saveCourseBtn').addEventListener('click', function() {
                const courseCode = document.getElementById('courseCode').value;
                const courseName = document.getElementById('courseName').value;
                if (courseCode && courseName) {
                    alert(`Cours "${courseCode} - ${courseName}" ajouté avec succès!`);
                    closeModal('addCourseModal');
                    // Ici, vous pouvez ajouter le code pour sauvegarder le cours
                } else {
                    alert('Veuillez remplir tous les champs obligatoires');
                }
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_SESSION["success"])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '<?= addslashes($_SESSION["success"]) ?>',
                    showConfirmButton: false,
                    timer: 2000
                });
            <?php unset($_SESSION["success"]);
            endif; ?>

            <?php if (isset($_SESSION["error"])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '<?= addslashes($_SESSION["error"]) ?>'
                });
            <?php unset($_SESSION["error"]);
            endif; ?>
        });
    </script>
</body>

</html>