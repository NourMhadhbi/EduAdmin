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
    <title>Gestion des Cours - Espace Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #1abc9c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .course-card {
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            height: 100%;
            cursor: pointer;
            overflow: hidden;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .course-header {
            border-radius: 12px 12px 0 0;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
        }

        .course-body {
            padding: 25px;
        }

        .course-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-course {
            width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border: 1px solid #eef2f7;
        }

        .session-item {
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
        }

        .session-item:hover {
            background-color: #f0f4f8;
            transform: translateX(5px);
        }

        .session-upcoming {
            border-left-color: var(--accent-color);
            background-color: #e8f6f3;
        }

        .session-completed {
            border-left-color: var(--success-color);
            background-color: #e8f5e9;
        }

        .session-cancelled {
            border-left-color: var(--danger-color);
            background-color: #fde8e8;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-color), var(--accent-color));
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .timeline-item.upcoming::before {
            background-color: var(--accent-color);
            box-shadow: 0 0 0 2px var(--accent-color);
        }

        .timeline-item.completed::before {
            background-color: var(--success-color);
            box-shadow: 0 0 0 2px var(--success-color);
        }

        /* Styles pour les modals */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 20px 25px;
            background-color: #fafbfc;
            border-radius: 12px 12px 0 0;
        }

        .modal-footer {
            border-top: 1px solid #eef2f7;
            padding: 20px 25px;
            background-color: #fafbfc;
            border-radius: 0 0 12px 12px;
        }

        .modal-body {
            padding: 25px;
        }

        .course-detail-modal .modal-dialog {
            max-width: 95%;
            width: 95%;
            height: 95vh;
        }

        .course-detail-modal .modal-content {
            height: 100%;
        }

        .course-detail-modal .modal-body {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .modal-course-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 25px;
            border-radius: 12px 12px 0 0;
        }

        /* Amélioration des formulaires */
        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }

        /* Boutons améliorés */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #2980b9);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            border: none;
        }

        /* Tableaux améliorés */
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #eef2f7;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--secondary-color);
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges améliorés */
        .badge {
            border-radius: 6px;
            padding: 6px 10px;
            font-weight: 600;
        }

        /* Cartes améliorées */
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eef2f7;
            padding: 20px;
            font-weight: 600;
            color: var(--secondary-color);
            border-radius: 12px 12px 0 0 !important;
        }




        /* Stat cards améliorées */
        .stat-card {
            text-align: center;
            padding: 25px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary-color);
            display: block;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Présence status */
        .presence-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .presence-present {
            background-color: #d4f5e2;
            color: #27ae60;
        }

        .presence-absent {
            background-color: #fde8e8;
            color: #e74c3c;
        }

        .presence-late {
            background-color: #fef5e6;
            color: #f39c12;
        }

        /* Page header amélioré */
        .page-header {
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid #eef2f7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .course-stats {
                flex-direction: column;
                gap: 10px;
            }

            .stat-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .stat-value {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <?php include("../NavBar/navbar.php") ?>

    <div class="container-fluid">
        <div class="row">


            <main class="px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
                    <h1 class="h2 fw-bold text-dark">Gestion des cours</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addCourseModal">
                        <i class="fas fa-plus me-2"></i> Nouveau cours
                    </button>
                </div>

                <!-- Filtres -->
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="courseFilter" class="form-label">Filtrer par matière</label>
                            <select class="form-select" id="courseFilter">
                                <option selected>Toutes les matières</option>
                                <option>Informatique</option>
                                <option>Mathématiques</option>
                                <option>Physique</option>
                                <option>Chimie</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="statusFilter" class="form-label">Filtrer par statut</label>
                            <select class="form-select" id="statusFilter">
                                <option selected>Tous les statuts</option>
                                <option>Actif</option>
                                <option>Terminé</option>
                                <option>À venir</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="searchCourse" class="form-label">Rechercher</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchCourse" placeholder="Nom du cours...">
                                <button class="btn btn-outline-primary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des cours -->
                <div class="row">
                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card course-card" data-bs-toggle="modal" data-bs-target="#courseDetailModal"
                            data-course="web">
                            <div class="course-header" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                                <h5 class="card-title fw-bold">Développement Web Avancé</h5>
                                <p class="card-text mb-0">WEB-2025-A</p>
                            </div>
                            <div class="course-body">
                                <p class="card-text text-muted">Cours avancé sur les technologies web modernes incluant
                                    les frameworks JavaScript, les API REST, et les bonnes pratiques de développement.
                                </p>
                                <div class="course-stats">
                                    <div class="stat-item">
                                        <span class="stat-value">35</span>
                                        <span class="stat-label">Étudiants</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">87%</span>
                                        <span class="stat-label">Présence</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">7/12</span>
                                        <span class="stat-label">Séances</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-success">Actif</span>
                                    <span class="badge bg-secondary ms-1">Informatique</span>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-outline-primary btn-course">
                                        <i class="fas fa-eye me-1"></i> Voir le détail
                                    </button>
                                    <button class="btn btn-outline-secondary btn-course" data-bs-toggle="modal"
                                        data-bs-target="#editCourseModal">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card course-card" data-bs-toggle="modal" data-bs-target="#courseDetailModal"
                            data-course="database">
                            <div class="course-header" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                                <h5 class="card-title fw-bold">Base de Données</h5>
                                <p class="card-text mb-0">BD-2025-B</p>
                            </div>
                            <div class="course-body">
                                <p class="card-text text-muted">Introduction aux systèmes de gestion de bases de
                                    données.</p>
                                <div class="course-stats">
                                    <div class="stat-item">
                                        <span class="stat-value">28</span>
                                        <span class="stat-label">Étudiants</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">92%</span>
                                        <span class="stat-label">Présence</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">6/10</span>
                                        <span class="stat-label">Séances</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-success">Actif</span>
                                    <span class="badge bg-secondary ms-1">Informatique</span>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-outline-primary btn-course">
                                        <i class="fas fa-eye me-1"></i> Voir le détail
                                    </button>
                                    <button class="btn btn-outline-secondary btn-course" data-bs-toggle="modal"
                                        data-bs-target="#editCourseModal">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card course-card" data-bs-toggle="modal" data-bs-target="#courseDetailModal"
                            data-course="algo">
                            <div class="course-header" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                                <h5 class="card-title fw-bold">Algorithmique</h5>
                                <p class="card-text mb-0">ALGO-2025-C</p>
                            </div>
                            <div class="course-body">
                                <p class="card-text text-muted">Fondements de l'algorithmique et structures de données.
                                </p>
                                <div class="course-stats">
                                    <div class="stat-item">
                                        <span class="stat-value">32</span>
                                        <span class="stat-label">Étudiants</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">85%</span>
                                        <span class="stat-label">Présence</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">5/8</span>
                                        <span class="stat-label">Séances</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-success">Actif</span>
                                    <span class="badge bg-secondary ms-1">Informatique</span>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-outline-primary btn-course">
                                        <i class="fas fa-eye me-1"></i> Voir le détail
                                    </button>
                                    <button class="btn btn-outline-secondary btn-course" data-bs-toggle="modal"
                                        data-bs-target="#editCourseModal">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de détail du cours -->
    <div class="modal fade course-detail-modal" id="courseDetailModal" tabindex="-1"
        aria-labelledby="courseDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-course-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="modal-title mb-1 fw-bold" id="courseDetailModalLabel">Développement Web Avancé
                            </h4>
                            <p class="mb-0 opacity-75">WEB-2025-A</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="fw-bold text-dark mb-3">Description du cours</h5>
                            <p class="text-muted">Cours avancé sur les technologies web modernes incluant les frameworks
                                JavaScript, les API REST, et les bonnes pratiques de développement.</p>

                            <div class="row mb-4">
                                <div class="col-md-3 mb-2">
                                    <strong class="text-dark">Code:</strong><br>
                                    <span class="text-muted">WEB-2025-A</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong class="text-dark">Crédits:</strong><br>
                                    <span class="text-muted">5</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong class="text-dark">Étudiants:</strong><br>
                                    <span class="text-muted">35</span>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <strong class="text-dark">Présence:</strong><br>
                                    <span class="badge bg-success">87%</span>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <button class="btn btn-primary me-2" data-bs-toggle="modal"
                                    data-bs-target="#planSessionModal">
                                    <i class="fas fa-calendar-plus me-1"></i> Planifier une séance
                                </button>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#editCourseModal">
                                    <i class="fas fa-edit me-1"></i> Modifier le cours
                                </button>
                            </div>

                            <h5 class="fw-bold text-dark mb-3">Séances planifiées</h5>
                            <div class="timeline">
                                <div class="timeline-item upcoming">
                                    <div class="session-item session-upcoming">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">Séance 8: Frameworks Frontend</h6>
                                                <p class="mb-1 text-muted">13 Oct. 2025, 14:00 - 16:00</p>
                                                <small class="text-muted"><i
                                                        class="fas fa-map-marker-alt me-1"></i>Salle 302 |
                                                    Présentiel</small>
                                            </div>
                                            <span class="badge bg-primary ms-2">À venir</span>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#editSessionModal">
                                                <i class="fas fa-edit me-1"></i> Modifier
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal"
                                                data-bs-target="#deleteSessionModal">
                                                <i class="fas fa-trash me-1"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="session-item session-completed">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">Séance 7: API REST avancées</h6>
                                                <p class="mb-1 text-muted">10 Oct. 2025, 14:00 - 16:00</p>
                                                <small class="text-muted"><i
                                                        class="fas fa-map-marker-alt me-1"></i>Salle 302 |
                                                    Présentiel</small>
                                            </div>
                                            <span class="badge bg-success ms-2">Terminée</span>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-light text-dark"><i class="fas fa-users me-1"></i>
                                                32/35 présents</span>
                                            <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal"
                                                data-bs-target="#viewAttendanceModal">
                                                <i class="fas fa-eye me-1"></i> Voir présence
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="session-item session-completed">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">Séance 6: Authentification et sécurité</h6>
                                                <p class="mb-1 text-muted">03 Oct. 2025, 14:00 - 16:00</p>
                                                <small class="text-muted"><i class="fas fa-video me-1"></i>En ligne |
                                                    Teams</small>
                                            </div>
                                            <span class="badge bg-success ms-2">Terminée</span>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-light text-dark"><i class="fas fa-users me-1"></i>
                                                30/35 présents</span>
                                            <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal"
                                                data-bs-target="#viewAttendanceModal">
                                                <i class="fas fa-eye me-1"></i> Voir présence
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">Informations du cours</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong class="text-dark">Période:</strong><br>
                                        <span class="text-muted">01 Sep. 2025 - 20 Dec. 2025</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong class="text-dark">Jour de cours:</strong><br>
                                        <span class="text-muted">Lundi et Jeudi</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong class="text-dark">Heure:</strong><br>
                                        <span class="text-muted">14:00 - 16:00</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong class="text-dark">Salle:</strong><br>
                                        <span class="text-muted">302 (Bâtiment Principal)</span>
                                    </div>
                                    <div class="mb-0">
                                        <strong class="text-dark">Enseignant:</strong><br>
                                        <span class="text-muted">Prof. Martin Dupont</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">Statistiques rapides</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Taux de présence moyen:</span>
                                        <strong class="text-dark">87%</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Séances terminées:</span>
                                        <strong class="text-dark">7/12</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Prochaine séance:</span>
                                        <strong class="text-dark">13 Oct.</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Étudiants à risque:</span>
                                        <strong class="text-danger">3</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">Actions rapides</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary">
                                            <i class="fas fa-users me-1"></i> Gérer les étudiants
                                        </button>
                                        <button class="btn btn-outline-primary">
                                            <i class="fas fa-file-export me-1"></i> Exporter les présences
                                        </button>
                                        <button class="btn btn-outline-primary">
                                            <i class="fas fa-chart-bar me-1"></i> Voir les statistiques
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de cours -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addCourseModalLabel">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>Créer un nouveau cours
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseName" class="form-label">Nom du cours</label>
                                <input type="text" class="form-control" id="courseName"
                                    placeholder="Ex: Développement Web Avancé" required>
                            </div>
                            <div class="col-md-6">
                                <label for="courseCode" class="form-label">Code du cours</label>
                                <input type="text" class="form-control" id="courseCode" placeholder="Ex: WEB-2025-A"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="courseDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="courseDescription" rows="3"
                                placeholder="Description détaillée du cours..."></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseSubject" class="form-label">Matière</label>
                                <select class="form-select" id="courseSubject" required>
                                    <option selected disabled>Sélectionnez une matière</option>
                                    <option>Informatique</option>
                                    <option>Mathématiques</option>
                                    <option>Physique</option>
                                    <option>Chimie</option>
                                    <option>Biologie</option>
                                    <option>Histoire</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="courseCredits" class="form-label">Crédits</label>
                                <input type="number" class="form-control" id="courseCredits" min="1" max="10" value="3">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="startDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="endDate" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="endDate" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseDays" class="form-label">Jours de cours</label>
                                <select class="form-select" id="courseDays" multiple>
                                    <option value="lundi">Lundi</option>
                                    <option value="mardi">Mardi</option>
                                    <option value="mercredi">Mercredi</option>
                                    <option value="jeudi">Jeudi</option>
                                    <option value="vendredi">Vendredi</option>
                                </select>
                                <small class="text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs
                                    jours</small>
                            </div>
                            <div class="col-md-6">
                                <label for="courseTime" class="form-label">Heure du cours</label>
                                <div class="input-group">
                                    <input type="time" class="form-control" id="courseStartTime">
                                    <span class="input-group-text">à</span>
                                    <input type="time" class="form-control" id="courseEndTime">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="courseLocation" class="form-label">Lieu/Salle</label>
                            <input type="text" class="form-control" id="courseLocation"
                                placeholder="Ex: Salle 302, Bâtiment Principal">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary">Créer le cours</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification de cours -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editCourseModalLabel">
                        <i class="fas fa-edit me-2 text-primary"></i>Modifier le cours
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editCourseName" class="form-label">Nom du cours</label>
                                <input type="text" class="form-control" id="editCourseName"
                                    value="Développement Web Avancé" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editCourseCode" class="form-label">Code du cours</label>
                                <input type="text" class="form-control" id="editCourseCode" value="WEB-2025-A" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editCourseDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editCourseDescription"
                                rows="3">Cours avancé sur les technologies web modernes incluant les frameworks JavaScript, les API REST, et les bonnes pratiques de développement.</textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editCourseSubject" class="form-label">Matière</label>
                                <select class="form-select" id="editCourseSubject" required>
                                    <option>Informatique</option>
                                    <option>Mathématiques</option>
                                    <option>Physique</option>
                                    <option>Chimie</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editCourseCredits" class="form-label">Crédits</label>
                                <input type="number" class="form-control" id="editCourseCredits" min="1" max="10"
                                    value="5">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editStartDate" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="editStartDate" value="2025-09-01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEndDate" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="editEndDate" value="2025-12-20" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editCourseDays" class="form-label">Jours de cours</label>
                                <select class="form-select" id="editCourseDays" multiple>
                                    <option value="lundi" selected>Lundi</option>
                                    <option value="mardi">Mardi</option>
                                    <option value="mercredi">Mercredi</option>
                                    <option value="jeudi" selected>Jeudi</option>
                                    <option value="vendredi">Vendredi</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editCourseTime" class="form-label">Heure du cours</label>
                                <div class="input-group">
                                    <input type="time" class="form-control" id="editCourseStartTime" value="14:00">
                                    <span class="input-group-text">à</span>
                                    <input type="time" class="form-control" id="editCourseEndTime" value="16:00">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editCourseLocation" class="form-label">Lieu/Salle</label>
                            <input type="text" class="form-control" id="editCourseLocation"
                                value="Salle 302, Bâtiment Principal">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de planification de séance -->
    <div class="modal fade" id="planSessionModal" tabindex="-1" aria-labelledby="planSessionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="planSessionModalLabel">
                        <i class="fas fa-calendar-plus me-2 text-primary"></i>Planifier une nouvelle séance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="sessionTitle" class="form-label">Titre de la séance</label>
                            <input type="text" class="form-control" id="sessionTitle"
                                placeholder="Ex: Introduction à React" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sessionDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="sessionDate" required>
                            </div>
                            <div class="col-md-3">
                                <label for="startTime" class="form-label">Heure de début</label>
                                <input type="time" class="form-control" id="startTime" required>
                            </div>
                            <div class="col-md-3">
                                <label for="endTime" class="form-label">Heure de fin</label>
                                <input type="time" class="form-control" id="endTime" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="sessionType" class="form-label">Type de séance</label>
                            <select class="form-select" id="sessionType" required>
                                <option value="presentiel">Présentiel</option>
                                <option value="en-ligne">En ligne</option>
                                <option value="hybride">Hybride</option>
                            </select>
                        </div>
                        <div class="mb-3" id="locationField">
                            <label for="sessionLocation" class="form-label">Lieu/Salle</label>
                            <input type="text" class="form-control" id="sessionLocation"
                                placeholder="Ex: Salle 302 ou Lien Teams">
                        </div>
                        <div class="mb-3">
                            <label for="sessionDescription" class="form-label">Description (optionnel)</label>
                            <textarea class="form-control" id="sessionDescription" rows="3"
                                placeholder="Objectifs de la séance..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sessionMaterials" class="form-label">Documents et ressources</label>
                            <input type="file" class="form-control" id="sessionMaterials" multiple>
                            <small class="text-muted">Vous pouvez ajouter plusieurs fichiers (PDF, PowerPoint,
                                etc.)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary">Planifier la séance</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification de séance -->
    <div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editSessionModalLabel">
                        <i class="fas fa-edit me-2 text-primary"></i>Modifier la séance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="editSessionTitle" class="form-label">Titre de la séance</label>
                            <input type="text" class="form-control" id="editSessionTitle"
                                value="Séance 8: Frameworks Frontend" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editSessionDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="editSessionDate" value="2025-10-13"
                                    required>
                            </div>
                            <div class="col-md-3">
                                <label for="editStartTime" class="form-label">Heure de début</label>
                                <input type="time" class="form-control" id="editStartTime" value="14:00" required>
                            </div>
                            <div class="col-md-3">
                                <label for="editEndTime" class="form-label">Heure de fin</label>
                                <input type="time" class="form-control" id="editEndTime" value="16:00" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editSessionType" class="form-label">Type de séance</label>
                            <select class="form-select" id="editSessionType" required>
                                <option value="presentiel" selected>Présentiel</option>
                                <option value="en-ligne">En ligne</option>
                                <option value="hybride">Hybride</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editSessionLocation" class="form-label">Lieu/Salle</label>
                            <input type="text" class="form-control" id="editSessionLocation" value="Salle 302">
                        </div>
                        <div class="mb-3">
                            <label for="editSessionDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editSessionDescription"
                                rows="3">Introduction aux frameworks frontend modernes : React, Vue.js et Angular.</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteSessionModal" tabindex="-1" aria-labelledby="deleteSessionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger" id="deleteSessionModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5 class="fw-bold">Êtes-vous sûr de vouloir supprimer cette séance ?</h5>
                        <p class="text-muted">Cette action est irréversible. Toutes les données de présence associées à
                            cette séance seront également supprimées.</p>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Séance à supprimer :</strong> Séance 8: Frameworks Frontend (13 Oct. 2025)
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger">Supprimer définitivement</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de visualisation des présences -->
    <div class="modal fade" id="viewAttendanceModal" tabindex="-1" aria-labelledby="viewAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="viewAttendanceModalLabel">
                        <i class="fas fa-users me-2 text-primary"></i>Présences - Séance 7: API REST avancées
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="mb-1 text-muted"><i class="fas fa-calendar me-1"></i>10 Oct. 2025, 14:00 - 16:00
                            </p>
                            <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-1"></i>Salle 302 | Présentiel
                            </p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-1">32/35 présents</h5>
                            <span class="badge bg-success">91% de présence</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Matricule</th>
                                    <th>Statut</th>
                                    <th>Heure d'arrivée</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3"
                                                alt="Avatar">
                                            <div>
                                                <strong>Thomas Leroy</strong>
                                                <div class="text-muted small">thomas.leroy@etudiant.univ.fr</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>ET2025001</td>
                                    <td><span class="presence-status presence-present">Présent</span></td>
                                    <td>13:58</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3"
                                                alt="Avatar">
                                            <div>
                                                <strong>Marie Petit</strong>
                                                <div class="text-muted small">marie.petit@etudiant.univ.fr</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>ET2025002</td>
                                    <td><span class="presence-status presence-present">Présent</span></td>
                                    <td>14:02</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3"
                                                alt="Avatar">
                                            <div>
                                                <strong>Jean Moreau</strong>
                                                <div class="text-muted small">jean.moreau@etudiant.univ.fr</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>ET2025003</td>
                                    <td><span class="presence-status presence-late">En retard</span></td>
                                    <td>14:15</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40" class="rounded-circle me-3"
                                                alt="Avatar">
                                            <div>
                                                <strong>Sophie Martin</strong>
                                                <div class="text-muted small">sophie.martin@etudiant.univ.fr</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>ET2025004</td>
                                    <td><span class="presence-status presence-absent">Absent</span></td>
                                    <td>-</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-file-export me-1"></i> Exporter en PDF
                            </button>
                            <button class="btn btn-outline-primary ms-2">
                                <i class="fas fa-file-excel me-1"></i> Exporter en Excel
                            </button>
                        </div>
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion de l'affichage du champ lieu en fonction du type de séance
        document.getElementById('sessionType').addEventListener('change', function() {
            const locationField = document.getElementById('locationField');
            const locationInput = document.getElementById('sessionLocation');

            if (this.value === 'presentiel') {
                locationInput.placeholder = 'Ex: Salle 302';
            } else if (this.value === 'en-ligne') {
                locationInput.placeholder = 'Ex: Lien de la visioconférence';
            } else {
                locationInput.placeholder = 'Ex: Salle 302 + Lien de visio';
            }
        });

        // Gestion du clic sur les cartes de cours
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Empêcher l'ouverture du modal si on clique sur les boutons
                if (!e.target.closest('.btn-course')) {
                    const courseType = this.getAttribute('data-course');
                    // Ici vous pouvez charger des données spécifiques au cours si nécessaire
                    console.log('Ouverture du cours:', courseType);
                }
            });
        });

        // Empêcher la propagation du clic sur les boutons
        document.querySelectorAll('.btn-course').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Définir la date d'aujourd'hui comme date minimale pour les champs de date
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('sessionDate').min = today;
        document.getElementById('startDate').min = today;
    </script>
</body>

</html>