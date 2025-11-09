<?php
include("../../Model/admin.php");
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';
$enseignants = Admin::getEnseignants();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduAdmin - Suivi & Contrôle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5f7fb;
            color: var(--dark);
        }

        /* Header */
        .header {
            height: 70px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .user-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Navigation */
        .nav {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-menu {
            display: flex;
            list-style: none;
        }

        .nav-menu li {
            padding: 0;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            border-bottom: 3px solid transparent;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-bottom: 3px solid var(--success);
        }

        .nav-menu i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .page-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .breadcrumb {
            display: flex;
            list-style: none;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .breadcrumb li:not(:last-child)::after {
            content: "/";
            margin: 0 10px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 0 20px;
        }

        .tab {
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-body h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .card-body p {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Pie chart container - made smaller */
        .pie-chart-container {
            height: 350px;
        }

        /* Tables */
        .table-container {
            background-color: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }

        .btn-danger {
            background-color: #e63946;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c1121f;
        }

        .btn-success {
            background-color: #2a9d8f;
            color: white;
        }

        .btn-success:hover {
            background-color: #21867a;
        }

        .form-control {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border 0.3s;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: var(--dark);
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .report-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .report-option {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .report-option:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .report-option.selected {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.1);
        }

        .report-option input {
            margin-right: 8px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                flex-direction: column;
            }

            .nav-menu a {
                justify-content: center;
            }

            .tabs {
                flex-direction: column;
            }

            .chart-row {
                grid-template-columns: 1fr;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .report-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<!-- Navigation -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Content Area -->
    <div class="content">
        <div class="tabs">
            <div class="tab active" data-tab="courses">Gestion des Cours</div>
            <div class="tab" data-tab="attendance">Suivi de Présence</div>
            <div class="tab" data-tab="statistics">Statistiques Globales</div>
        </div>

        <!-- Gestion des Cours Tab -->
        <div class="tab-content active" id="courses-tab">
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours Actifs</div>
                        <div class="card-icon" style="background-color: var(--success);">
                            <i class="fas fa-play-circle"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>142</h3>
                        <p>En cours cette semaine</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours Terminés</div>
                        <div class="card-icon" style="background-color: var(--info);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>89</h3>
                        <p>Cette année académique</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours à Venir</div>
                        <div class="card-icon" style="background-color: var(--warning);">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>24</h3>
                        <p>Programmés pour le mois prochain</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Tous les Cours</div>
                    <button class="btn btn-primary" id="addCourseBtn">Ajouter un Cours</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom du Cours</th>
                            <th>Enseignant</th>
                            <th>Date de Début</th>
                            <th>Date de Fin</th>
                            <th>Étudiants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>INF301</td>
                            <td>Algorithmes Avancés</td>
                            <td>Prof. Dubois</td>
                            <td>15 Sep 2023</td>
                            <td>15 Jan 2024</td>
                            <td>45</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary edit-course-btn"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-course-btn"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>MAT205</td>
                            <td>Analyse Complexe</td>
                            <td>Prof. Laurent</td>
                            <td>10 Sep 2023</td>
                            <td>10 Jan 2024</td>
                            <td>32</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary edit-course-btn"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-course-btn"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Suivi de Présence Tab -->
        <div class="tab-content" id="attendance-tab">
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Présence Globale</div>
                        <div class="card-icon" style="background-color: var(--primary);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>87.5%</h3>
                        <p>Moyenne générale</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Présence Étudiants</div>
                        <div class="card-icon" style="background-color: var(--info);">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>85.2%</h3>
                        <p>Ce mois-ci</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Présence Enseignants</div>
                        <div class="card-icon" style="background-color: var(--success);">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>94.7%</h3>
                        <p>Ce mois-ci</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Détails de Présence</div>
                    <div>
                        <select class="form-control" id="courseSelect" style="width: auto; display: inline-block;">
                            <option>Sélectionner un cours</option>
                            <option>Algorithmes Avancés</option>
                            <option>Analyse Complexe</option>
                        </select>
                        <button class="btn btn-primary" id="generateReportBtn">Générer Rapport</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Cours</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Marie Dupont</td>
                            <td>Algorithmes Avancés</td>
                            <td>10 Nov 2023</td>
                            <td><span class="badge badge-success">Présent</span></td>
                            <td>10:00 - 12:00</td>
                        </tr>
                        <tr>
                            <td>Jean Martin</td>
                            <td>Algorithmes Avancés</td>
                            <td>10 Nov 2023</td>
                            <td><span class="badge badge-warning">Absent</span></td>
                            <td>10:00 - 12:00</td>
                        </tr>
                        <tr>
                            <td>Sophie Bernard</td>
                            <td>Analyse Complexe</td>
                            <td>09 Nov 2023</td>
                            <td><span class="badge badge-success">Présent</span></td>
                            <td>14:00 - 16:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistiques Globales Tab -->
        <div class="tab-content" id="statistics-tab">
            <div class="chart-row">
                <div class="chart-container">
                    <div class="chart-title">Évolution des Inscriptions</div>
                    <canvas id="enrollmentChart"></canvas>
                </div>
                <div class="chart-container pie-chart-container">
                    <div class="chart-title">Répartition par Département</div>
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-container">
                    <div class="chart-title">Performance des Cours</div>
                    <canvas id="performanceChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-title">Taux de Réussite</div>
                    <canvas id="successRateChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Statistiques Détaillées</div>
                    <button class="btn btn-primary" id="exportDataBtn">Exporter Données</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Métrique</th>
                            <th>Valeur</th>
                            <th>Évolution</th>
                            <th>Tendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Nombre total d'étudiants</td>
                            <td>1,245</td>
                            <td>+5%</td>
                            <td><i class="fas fa-arrow-up" style="color: #4cc9f0;"></i></td>
                        </tr>
                        <tr>
                            <td>Nombre total d'enseignants</td>
                            <td>68</td>
                            <td>+3%</td>
                            <td><i class="fas fa-arrow-up" style="color: #4cc9f0;"></i></td>
                        </tr>
                        <tr>
                            <td>Nombre de cours actifs</td>
                            <td>142</td>
                            <td>+12%</td>
                            <td><i class="fas fa-arrow-up" style="color: #4cc9f0;"></i></td>
                        </tr>
                        <tr>
                            <td>Taux de présence global</td>
                            <td>87.5%</td>
                            <td>+2.3%</td>
                            <td><i class="fas fa-arrow-up" style="color: #4cc9f0;"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal pour Ajouter un Cours -->
    <!-- <div class="modal" id="addCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un Nouveau Cours</h3>
                <button class="modal-close" data-modal="addCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="courseCode">Code du Cours</label>
                            <input type="text" class="form-control" id="courseCode" placeholder="Ex: INF301" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="courseName">Nom du Cours</label>
                            <input type="text" class="form-control" id="courseName"
                                placeholder="Ex: Algorithmes Avancés" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="courseTeacher">Enseignant</label>
                        <select class="form-control" id="courseTeacher" required>
                            <option value="">Sélectionner un enseignant</option>
                            <option value="Prof. Dubois">Prof. Dubois</option>
                            <option value="Prof. Laurent">Prof. Laurent</option>
                            <option value="Prof. Martin">Prof. Martin</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="startDate">Date de Début</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="endDate">Date de Fin</label>
                            <input type="date" class="form-control" id="endDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="maxStudents">Nombre Maximum d'Étudiants</label>
                        <input type="number" class="form-control" id="maxStudents" min="1" max="100" value="30"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="courseDescription">Description du Cours</label>
                        <textarea class="form-control" id="courseDescription" rows="3"
                            placeholder="Description du cours..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="addCourseModal">Annuler</button>
                <button class="btn btn-primary" id="saveCourseBtn">Enregistrer le Cours</button>
            </div>
        </div>
    </div> -->
<!-- Modal pour Ajouter un Cours -->
<div class="modal" id="addCourseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter un Nouveau Cours</h3>
            <button class="modal-close" data-modal="addCourseModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addCourseForm" action="../../Controller/AdminController.php" method="POST">
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
                        <?php foreach ($enseignants as $ens): ?>
                            <option value="<?= htmlspecialchars($ens['id']) ?>">
                                <?= htmlspecialchars($ens['nom']) ?>  <?= htmlspecialchars($ens['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="courseDescription">Description du Cours</label>
                    <textarea class="form-control" id="courseDescription" name="description" rows="3"
                        placeholder="Description du cours..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" data-modal="addCourseModal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="saveCourseBtn">Enregistrer le Cours</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Modal pour Modifier un Cours -->
    <div class="modal" id="editCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier le Cours</h3>
                <button class="modal-close" data-modal="editCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editCourseCode">Code du Cours</label>
                            <input type="text" class="form-control" id="editCourseCode" placeholder="Ex: INF301"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editCourseName">Nom du Cours</label>
                            <input type="text" class="form-control" id="editCourseName"
                                placeholder="Ex: Algorithmes Avancés" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editCourseTeacher">Enseignant</label>
                        <select class="form-control" id="editCourseTeacher" required>
                            <option value="">Sélectionner un enseignant</option>
                            <option value="Prof. Dubois">Prof. Dubois</option>
                            <option value="Prof. Laurent">Prof. Laurent</option>
                            <option value="Prof. Martin">Prof. Martin</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editStartDate">Date de Début</label>
                            <input type="date" class="form-control" id="editStartDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editEndDate">Date de Fin</label>
                            <input type="date" class="form-control" id="editEndDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editMaxStudents">Nombre Maximum d'Étudiants</label>
                        <input type="number" class="form-control" id="editMaxStudents" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editCourseDescription">Description du Cours</label>
                        <textarea class="form-control" id="editCourseDescription" rows="3"
                            placeholder="Description du cours..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="editCourseModal">Annuler</button>
                <button class="btn btn-success" id="updateCourseBtn">Mettre à Jour</button>
            </div>
        </div>
    </div>

    <!-- Modal pour Supprimer un Cours -->
    <div class="modal" id="deleteCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirmer la Suppression</h3>
                <button class="modal-close" data-modal="deleteCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le cours <strong id="courseToDeleteName"></strong> ?</p>
                <p class="text-danger">Cette action est irréversible et supprimera toutes les données associées à ce
                    cours.</p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="deleteCourseModal">Annuler</button>
                <button class="btn btn-danger" id="confirmDeleteCourseBtn">Supprimer Définitivement</button>
            </div>
        </div>
    </div>

    <!-- Modal pour Générer un Rapport -->
    <div class="modal" id="generateReportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Générer un Rapport</h3>
                <button class="modal-close" data-modal="generateReportModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="reportCourse">Sélectionner un Cours</label>
                    <select class="form-control" id="reportCourse">
                        <option value="">Sélectionner un cours</option>
                        <option value="Algorithmes Avancés">Algorithmes Avancés</option>
                        <option value="Analyse Complexe">Analyse Complexe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Type de Rapport</label>
                    <div class="report-options">
                        <label class="report-option">
                            <input type="radio" name="reportType" value="presence" checked>
                            Rapport de Présence
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="performance">
                            Rapport de Performance
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="students">
                            Liste des Étudiants
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="detailed">
                            Rapport Détaillé
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="startDateRange">Date de Début</label>
                        <input type="date" class="form-control" id="startDateRange">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="endDateRange">Date de Fin</label>
                        <input type="date" class="form-control" id="endDateRange">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reportFormat">Format du Rapport</label>
                    <select class="form-control" id="reportFormat">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="generateReportModal">Annuler</button>
                <button class="btn btn-primary" id="confirmGenerateReportBtn">Générer le Rapport</button>
            </div>
        </div>
    </div>
       <?php include("../../Footer/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');

                // Update active tab
                this.parentElement.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                // Show corresponding tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modals when clicking on close buttons
        document.querySelectorAll('.modal-close, .modal-footer .btn:not(.btn-primary):not(.btn-success):not(.btn-danger)').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                closeModal(modalId);
            });
        });

        // Close modal when clicking outside the modal content
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Button functionalities
        document.getElementById('addCourseBtn').addEventListener('click', function() {
            openModal('addCourseModal');
        });

        document.querySelectorAll('.edit-course-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const courseCode = row.cells[0].textContent;
                const courseName = row.cells[1].textContent;
                const courseTeacher = row.cells[2].textContent;

                // Pre-fill the edit form with current data
                document.getElementById('editCourseCode').value = courseCode;
                document.getElementById('editCourseName').value = courseName;
                document.getElementById('editCourseTeacher').value = courseTeacher;

                openModal('editCourseModal');
            });
        });

        document.querySelectorAll('.delete-course-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const courseCode = row.cells[0].textContent;
                const courseName = row.cells[1].textContent;

                document.getElementById('courseToDeleteName').textContent = `${courseCode} - ${courseName}`;
                openModal('deleteCourseModal');
            });
        });

        document.getElementById('generateReportBtn').addEventListener('click', function() {
            openModal('generateReportModal');
        });

        document.getElementById('exportDataBtn').addEventListener('click', function() {
            alert('Export des données en cours...');
            // Ici, vous pouvez ajouter le code pour exporter les données
        });

        // Form submissions
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
        

        document.getElementById('updateCourseBtn').addEventListener('click', function() {
            const courseCode = document.getElementById('editCourseCode').value;
            const courseName = document.getElementById('editCourseName').value;

            if (courseCode && courseName) {
                alert(`Cours "${courseCode} - ${courseName}" modifié avec succès!`);
                closeModal('editCourseModal');
                // Ici, vous pouvez ajouter le code pour mettre à jour le cours
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });

        document.getElementById('confirmDeleteCourseBtn').addEventListener('click', function() {
            const courseName = document.getElementById('courseToDeleteName').textContent;
            alert(`Cours "${courseName}" supprimé avec succès!`);
            closeModal('deleteCourseModal');
            // Ici, vous pouvez ajouter le code pour supprimer le cours
        });

        document.getElementById('confirmGenerateReportBtn').addEventListener('click', function() {
            const selectedCourse = document.getElementById('reportCourse').value;
            const reportType = document.querySelector('input[name="reportType"]:checked').value;
            const reportFormat = document.getElementById('reportFormat').value;

            if (selectedCourse) {
                alert(`Génération du rapport ${reportType} pour ${selectedCourse} en format ${reportFormat}`);
                closeModal('generateReportModal');
                // Ici, vous pouvez ajouter le code pour générer le rapport
            } else {
                alert('Veuillez sélectionner un cours');
            }
        });

        // Report option selection styling
        document.querySelectorAll('.report-option input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.report-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.closest('.report-option').classList.add('selected');
            });
        });

        // Initialize selected report option
        document.querySelector('.report-option input:checked').closest('.report-option').classList.add('selected');

        // Charts
        // Enrollment Chart
        const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
        const enrollmentChart = new Chart(enrollmentCtx, {
            type: 'bar',
            data: {
                labels: ['2018', '2019', '2020', '2021', '2022', '2023'],
                datasets: [{
                    label: 'Nombre d\'étudiants',
                    data: [800, 950, 1050, 1120, 1180, 1245],
                    backgroundColor: '#4cc9f0'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Department Chart
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        const departmentChart = new Chart(departmentCtx, {
            type: 'pie',
            data: {
                labels: ['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Biologie', 'Autres'],
                datasets: [{
                    data: [35, 25, 15, 10, 8, 7],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0',
                        '#4895ef',
                        '#f72585',
                        '#7209b7'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceCtx, {
            type: 'radar',
            data: {
                labels: ['Algorithmes', 'Mathématiques', 'Physique', 'Programmation', 'Réseaux', 'Base de données'],
                datasets: [{
                    label: 'Performance Moyenne',
                    data: [85, 78, 90, 88, 82, 79],
                    backgroundColor: 'rgba(76, 201, 240, 0.2)',
                    borderColor: '#4cc9f0',
                    pointBackgroundColor: '#4cc9f0'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });

        // Success Rate Chart
        const successRateCtx = document.getElementById('successRateChart').getContext('2d');
        const successRateChart = new Chart(successRateCtx, {
            type: 'bar',
            data: {
                labels: ['L1', 'L2', 'L3', 'M1', 'M2'],
                datasets: [{
                    label: 'Taux de Réussite (%)',
                    data: [75, 82, 79, 85, 88],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0',
                        '#4895ef',
                        '#f72585'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script> -->
        <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');

                // Update active tab
                this.parentElement.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                // Show corresponding tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modals when clicking on close buttons
        document.querySelectorAll('.modal-close, .modal-footer .btn:not(.btn-primary):not(.btn-success):not(.btn-danger)').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                closeModal(modalId);
            });
        });

        // Close modal when clicking outside the modal content
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Button functionalities
        document.getElementById('addCourseBtn').addEventListener('click', function() {
            openModal('addCourseModal');
        });

        document.querySelectorAll('.edit-course-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const courseCode = row.cells[0].textContent;
                const courseName = row.cells[1].textContent;
                const courseTeacher = row.cells[2].textContent;

                // Pre-fill the edit form with current data
                document.getElementById('editCourseCode').value = courseCode;
                document.getElementById('editCourseName').value = courseName;
                document.getElementById('editCourseTeacher').value = courseTeacher;

                openModal('editCourseModal');
            });
        });

        document.querySelectorAll('.delete-course-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const courseCode = row.cells[0].textContent;
                const courseName = row.cells[1].textContent;

                document.getElementById('courseToDeleteName').textContent = `${courseCode} - ${courseName}`;
                openModal('deleteCourseModal');
            });
        });

        document.getElementById('generateReportBtn').addEventListener('click', function() {
            openModal('generateReportModal');
        });

        document.getElementById('exportDataBtn').addEventListener('click', function() {
            alert('Export des données en cours...');
            // Ici, vous pouvez ajouter le code pour exporter les données
        });

        // Form submissions
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

        document.getElementById('updateCourseBtn').addEventListener('click', function() {
            const courseCode = document.getElementById('editCourseCode').value;
            const courseName = document.getElementById('editCourseName').value;

            if (courseCode && courseName) {
                alert(`Cours "${courseCode} - ${courseName}" modifié avec succès!`);
                closeModal('editCourseModal');
                // Ici, vous pouvez ajouter le code pour mettre à jour le cours
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });

        document.getElementById('confirmDeleteCourseBtn').addEventListener('click', function() {
            const courseName = document.getElementById('courseToDeleteName').textContent;
            alert(`Cours "${courseName}" supprimé avec succès!`);
            closeModal('deleteCourseModal');
            // Ici, vous pouvez ajouter le code pour supprimer le cours
        });

        document.getElementById('confirmGenerateReportBtn').addEventListener('click', function() {
            const selectedCourse = document.getElementById('reportCourse').value;
            const reportType = document.querySelector('input[name="reportType"]:checked').value;
            const reportFormat = document.getElementById('reportFormat').value;

            if (selectedCourse) {
                alert(`Génération du rapport ${reportType} pour ${selectedCourse} en format ${reportFormat}`);
                closeModal('generateReportModal');
                // Ici, vous pouvez ajouter le code pour générer le rapport
            } else {
                alert('Veuillez sélectionner un cours');
            }
        });

        // Report option selection styling
        document.querySelectorAll('.report-option input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.report-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.closest('.report-option').classList.add('selected');
            });
        });

        // Initialize selected report option
        document.querySelector('.report-option input:checked').closest('.report-option').classList.add('selected');

        // Charts
        // Enrollment Chart
        const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
        const enrollmentChart = new Chart(enrollmentCtx, {
            type: 'bar',
            data: {
                labels: ['2018', '2019', '2020', '2021', '2022', '2023'],
                datasets: [{
                    label: 'Nombre d\'étudiants',
                    data: [800, 950, 1050, 1120, 1180, 1245],
                    backgroundColor: '#4cc9f0'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Department Chart
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        const departmentChart = new Chart(departmentCtx, {
            type: 'pie',
            data: {
                labels: ['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Biologie', 'Autres'],
                datasets: [{
                    data: [35, 25, 15, 10, 8, 7],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0',
                        '#4895ef',
                        '#f72585',
                        '#7209b7'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceCtx, {
            type: 'radar',
            data: {
                labels: ['Algorithmes', 'Mathématiques', 'Physique', 'Programmation', 'Réseaux', 'Base de données'],
                datasets: [{
                    label: 'Performance Moyenne',
                    data: [85, 78, 90, 88, 82, 79],
                    backgroundColor: 'rgba(76, 201, 240, 0.2)',
                    borderColor: '#4cc9f0',
                    pointBackgroundColor: '#4cc9f0'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });

        // Success Rate Chart
        const successRateCtx = document.getElementById('successRateChart').getContext('2d');
        const successRateChart = new Chart(successRateCtx, {
            type: 'bar',
            data: {
                labels: ['L1', 'L2', 'L3', 'M1', 'M2'],
                datasets: [{
                    label: 'Taux de Réussite (%)',
                    data: [75, 82, 79, 85, 88],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0',
                        '#4895ef',
                        '#f72585'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
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
    <?php unset($_SESSION["success"]); endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: '<?= addslashes($_SESSION["error"]) ?>'
        });
    <?php unset($_SESSION["error"]); endif; ?>
});
    </script>
</body>

</html>