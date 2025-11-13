<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'etudiant') {
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
    <title>Historique des Présences - Système de Présence Intelligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #2b2d42;
            --accent-color: #06d6a0;
            --accent-dark: #05c391;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --border-radius: 12px;
            --box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark-color);
            line-height: 1.5;
            min-height: 100vh;
            font-size: 0.9rem;
        }


        .user-profile-nav {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            font-size: 0.85rem;
        }

        .user-profile-nav:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .user-profile-nav i {
            margin-right: 6px;
            font-size: 1rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--primary-dark);
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        .page-title {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
            font-size: 1.8rem;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.2rem;
            background: white;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 600;
            padding: 1rem 1.2rem;
            border: none;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .card-header i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .card-body {
            padding: 1.2rem;
        }

        /* Stats Overview */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
            border-left: 3px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card.present {
            border-left-color: var(--success-color);
        }

        .stat-card.absent {
            border-left-color: var(--danger-color);
        }

        .stat-card.rate {
            border-left-color: var(--primary-color);
        }

        .stat-card.courses {
            border-left-color: var(--accent-color);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.2rem;
        }

        .stat-card.present .stat-icon {
            background: rgba(6, 214, 160, 0.1);
            color: var(--success-color);
        }

        .stat-card.absent .stat-icon {
            background: rgba(239, 71, 111, 0.1);
            color: var(--danger-color);
        }

        .stat-card.rate .stat-icon {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .stat-card.courses .stat-icon {
            background: rgba(255, 209, 102, 0.1);
            color: var(--warning-color);
        }

        .stat-number {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            line-height: 1.2;
        }

        .stat-card.present .stat-number {
            color: var(--success-color);
        }

        .stat-card.absent .stat-number {
            color: var(--danger-color);
        }

        .stat-card.rate .stat-number {
            color: var(--primary-color);
        }

        .stat-card.courses .stat-number {
            color: var(--warning-color);
        }

        .stat-label {
            color: var(--gray-medium);
            font-weight: 500;
            font-size: 0.8rem;
        }

        /* Filtres */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
        }

        .filter-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .filter-title {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .filter-title i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.8rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid var(--gray-light);
            transition: var(--transition);
            font-size: 0.85rem;
            height: auto;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.35);
        }

        .btn-outline-primary {
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border: 1.5px solid var(--gray-medium);
            color: var(--gray-medium);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: var(--gray-medium);
            color: white;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .table-title {
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .table-title i {
            margin-right: 8px;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .table-responsive {
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            overflow: hidden;
        }

        .table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 1.5px solid var(--gray-light);
            font-weight: 600;
            color: var(--secondary-color);
            padding: 0.8rem 1rem;
            vertical-align: middle;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }

        .table tbody td {
            padding: 0.8rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-light);
        }

        .presence-status {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-present {
            background: rgba(6, 214, 160, 0.12);
            color: #155724;
        }

        .status-absent {
            background: rgba(239, 71, 111, 0.12);
            color: #721c24;
        }

        .status-pending {
            background: rgba(255, 209, 102, 0.12);
            color: #856404;
        }

        .method-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .method-camera {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary-color);
        }

        .method-upload {
            background: rgba(6, 214, 160, 0.08);
            color: var(--success-color);
        }

        .method-none {
            background: rgba(108, 117, 125, 0.08);
            color: var(--gray-medium);
        }

        .justification-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .justified {
            background: rgba(6, 214, 160, 0.12);
            color: #155724;
        }

        .pending-justification {
            background: rgba(255, 193, 7, 0.12);
            color: #856404;
        }

        /* Statistiques par cours */
        .course-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .course-stat {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            box-shadow: var(--box-shadow);
        }

        .course-name {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }

        .course-percentage {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: var(--gray-light);
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 4px;
            transition: width 1s ease-in-out;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.2rem;
            background: #f8f9fa;
            border-top: 1px solid var(--gray-light);
        }

        .pagination-info {
            color: var(--gray-medium);
            font-size: 0.8rem;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid var(--gray-light);
            color: var(--primary-color);
            font-weight: 500;
            transition: var(--transition);
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }

            .pagination-container {
                flex-direction: column;
                gap: 0.8rem;
                text-align: center;
            }

            .course-stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <?php include("../NavBar/navbar.php") ?>

    <!-- Contenu principal -->
    <div class="container py-3">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="accueil.html"><i class="fas fa-home me-1"></i>Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Historique des présences</li>
                    </ol>
                </nav>

                <h1 class="page-title">Historique des Présences</h1>
            </div>
        </div>

        <!-- Statistiques globales -->
        <div class="stats-overview">
            <div class="stat-card rate">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number">85%</div>
                <div class="stat-label">Taux de présence</div>
            </div>
            <div class="stat-card present">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number">42</div>
                <div class="stat-label">Présences</div>
            </div>
            <div class="stat-card absent">
                <div class="stat-icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-number">8</div>
                <div class="stat-label">Absences</div>
            </div>
            <div class="stat-card courses">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number">12</div>
                <div class="stat-label">Cours suivis</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filter-section">
            <div class="filter-header">
                <div class="filter-title">
                    <i class="fas fa-filter"></i> Filtrer les résultats
                </div>
                <div class="filter-actions">
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo me-1"></i>Réinitialiser
                    </button>
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Appliquer
                    </button>
                </div>
            </div>
            <div class="filter-grid">
                <div>
                    <label for="courseFilter" class="form-label">Cours</label>
                    <select class="form-select" id="courseFilter">
                        <option value="">Tous les cours</option>
                        <option value="web">Développement Web</option>
                        <option value="bd">Bases de données</option>
                        <option value="ia">Intelligence Artificielle</option>
                    </select>
                </div>
                <div>
                    <label for="monthFilter" class="form-label">Période</label>
                    <select class="form-select" id="monthFilter">
                        <option value="">Toutes les périodes</option>
                        <option value="1">Janvier 2023</option>
                        <option value="2">Février 2023</option>
                        <option value="3">Mars 2023</option>
                        <option value="4">Avril 2023</option>
                        <option value="5" selected>Mai 2023</option>
                    </select>
                </div>
                <div>
                    <label for="statusFilter" class="form-label">Statut</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="present">Présent</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                <div>
                    <label for="methodFilter" class="form-label">Méthode</label>
                    <select class="form-select" id="methodFilter">
                        <option value="">Toutes les méthodes</option>
                        <option value="camera">Reconnaissance</option>
                        <option value="upload">Image uploadée</option>
                        <option value="none">Non marqué</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tableau des présences -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-history"></i> Détail des présences
                </div>
                <div class="table-actions">
                    <button class="btn btn-outline-light btn-sm">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <button class="btn btn-outline-light btn-sm">
                        <i class="fas fa-download me-1"></i>Exporter
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Cours</th>
                            <th>Heure</th>
                            <th>Enseignant</th>
                            <th>Statut</th>
                            <th>Méthode</th>
                            <th>Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>15/05/2023</strong></td>
                            <td>Développement Web Dynamique</td>
                            <td>09:00</td>
                            <td>Prof. Martin</td>
                            <td><span class="presence-status status-present"><i class="fas fa-check me-1"></i>Présent</span></td>
                            <td><span class="method-badge method-camera"><i class="fas fa-camera me-1"></i>Reconnaissance</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>12/05/2023</strong></td>
                            <td>Base de données avancées</td>
                            <td>11:30</td>
                            <td>Prof. Dubois</td>
                            <td><span class="presence-status status-present"><i class="fas fa-check me-1"></i>Présent</span></td>
                            <td><span class="method-badge method-camera"><i class="fas fa-camera me-1"></i>Reconnaissance</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>10/05/2023</strong></td>
                            <td>Intelligence Artificielle</td>
                            <td>14:30</td>
                            <td>Prof. Lefebvre</td>
                            <td><span class="presence-status status-absent"><i class="fas fa-times me-1"></i>Absent</span></td>
                            <td><span class="method-badge method-none"><i class="fas fa-times me-1"></i>Non marqué</span></td>
                            <td>
                                <button class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-pen me-1"></i>Justifier
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>08/05/2023</strong></td>
                            <td>Développement Web Dynamique</td>
                            <td>09:00</td>
                            <td>Prof. Martin</td>
                            <td><span class="presence-status status-present"><i class="fas fa-check me-1"></i>Présent</span></td>
                            <td><span class="method-badge method-camera"><i class="fas fa-camera me-1"></i>Reconnaissance</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>05/05/2023</strong></td>
                            <td>Base de données avancées</td>
                            <td>11:30</td>
                            <td>Prof. Dubois</td>
                            <td><span class="presence-status status-present"><i class="fas fa-check me-1"></i>Présent</span></td>
                            <td><span class="method-badge method-upload"><i class="fas fa-upload me-1"></i>Image uploadée</span></td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td><strong>03/05/2023</strong></td>
                            <td>Intelligence Artificielle</td>
                            <td>14:30</td>
                            <td>Prof. Lefebvre</td>
                            <td><span class="presence-status status-absent"><i class="fas fa-times me-1"></i>Absent</span></td>
                            <td><span class="method-badge method-none"><i class="fas fa-times me-1"></i>Non marqué</span></td>
                            <td>
                                <span class="justification-badge justified"><i class="fas fa-check me-1"></i>Justifié</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>01/05/2023</strong></td>
                            <td>Développement Web Dynamique</td>
                            <td>09:00</td>
                            <td>Prof. Martin</td>
                            <td><span class="presence-status status-present"><i class="fas fa-check me-1"></i>Présent</span></td>
                            <td><span class="method-badge method-camera"><i class="fas fa-camera me-1"></i>Reconnaissance</span></td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                <div class="pagination-info">
                    Affichage de 1 à 7 sur 50 entrées
                </div>
                <nav aria-label="Navigation des pages">
                    <ul class="pagination">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Précédent</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Statistiques par cours -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Statistiques par cours
            </div>
            <div class="card-body">
                <div class="course-stats">
                    <div class="course-stat">
                        <div class="course-name">
                            <span>Développement Web</span>
                            <span class="course-percentage">90%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 90%"></div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between text-muted small">
                            <span>36 présences</span>
                            <span>4 absences</span>
                        </div>
                    </div>
                    <div class="course-stat">
                        <div class="course-name">
                            <span>Bases de données</span>
                            <span class="course-percentage">85%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between text-muted small">
                            <span>34 présences</span>
                            <span>6 absences</span>
                        </div>
                    </div>
                    <div class="course-stat">
                        <div class="course-name">
                            <span>Intelligence Artificielle</span>
                            <span class="course-percentage">75%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%"></div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between text-muted small">
                            <span>30 présences</span>
                            <span>10 absences</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <?php include("../../Footer/footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation des barres de progression
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>

</html>