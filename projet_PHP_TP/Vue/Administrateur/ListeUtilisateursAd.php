<?php
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
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduAdmin - Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            gap: 8px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #3ab8e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 201, 240, 0.3);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e3166a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(247, 37, 133, 0.3);
        }

        .btn-danger {
            background-color: #e63946;
            color: white;
        }

        .btn-danger:hover {
            background-color: #d32f3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 57, 70, 0.3);
        }

        /* Search and Filters */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            background-color: white;
            cursor: pointer;
        }

        /* Modals - Version améliorée */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 500px;
            max-width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            animation: slideUp 0.3s ease forwards;
            overflow: hidden;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            transition: transform 0.3s;
        }

        .modal-close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #f9fafb;
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

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Confirmation Modal */
        .confirmation-modal .modal-content {
            width: 400px;
        }

        .confirmation-icon {
            text-align: center;
            font-size: 3rem;
            color: var(--warning);
            margin-bottom: 15px;
        }

        .confirmation-text {
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0;
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

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10px;
                width: calc(100% - 20px);
            }

            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .content {
                padding: 20px;
            }

            .header {
                padding: 15px 20px;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn-sm {
                padding: 8px 12px;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
 

    <!-- Navigation -->
      <?php include("../NavBar/navbar.php") ?>

    <!-- Content Area -->
    <div class="content">

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #4cc9f0;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>245</h3>
                    <p>Étudiants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #4361ee;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <h3>32</h3>
                    <p>Enseignants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #3a0ca3;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3>5</h3>
                    <p>Administrateurs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f72585;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>12</h3>
                    <p>En attente</p>
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="students">Étudiants</div>
            <div class="tab" data-tab="teachers">Enseignants</div>
            <div class="tab" data-tab="admins">Administrateurs</div>
        </div>

        <!-- Étudiants Tab -->
        <div class="tab-content active" id="students-tab">
            <div class="table-container">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un étudiant...">
                    </div>
                    <div class="filters">
                        <select class="filter-select">
                            <option>Tous les statuts</option>
                            <option>Actif</option>
                            <option>Inactif</option>
                        </select>
                        <select class="filter-select">
                            <option>Toutes les classes</option>
                            <option>L1 Informatique</option>
                            <option>L2 Informatique</option>
                            <option>L3 Informatique</option>
                        </select>
                    </div>
                </div>
                <div class="table-header">
                    <div class="table-title">Liste des Étudiants</div>
                    <button class="btn btn-primary" id="addStudentBtn">
                        <i class="fas fa-plus"></i> Ajouter un Étudiant
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Classe</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>STU001</td>
                            <td>Marie Dupont</td>
                            <td>marie.dupont@example.com</td>
                            <td>L3 Informatique</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('student', 'STU001')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="openDeleteModal('student', 'STU001', 'Marie Dupont')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>STU002</td>
                            <td>Jean Martin</td>
                            <td>jean.martin@example.com</td>
                            <td>M1 Mathématiques</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('student', 'STU002')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="openDeleteModal('student', 'STU002', 'Jean Martin')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>STU003</td>
                            <td>Sophie Bernard</td>
                            <td>sophie.bernard@example.com</td>
                            <td>L2 Physique</td>
                            <td><span class="badge badge-warning">Inactif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('student', 'STU003')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="openDeleteModal('student', 'STU003', 'Sophie Bernard')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enseignants Tab -->
        <div class="tab-content" id="teachers-tab">
            <div class="table-container">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un enseignant...">
                    </div>
                    <div class="filters">
                        <select class="filter-select">
                            <option>Tous les départements</option>
                            <option>Informatique</option>
                            <option>Mathématiques</option>
                            <option>Physique</option>
                        </select>
                    </div>
                </div>
                <div class="table-header">
                    <div class="table-title">Liste des Enseignants</div>
                    <button class="btn btn-primary" id="addTeacherBtn">
                        <i class="fas fa-plus"></i> Ajouter un Enseignant
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Département</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>TCH001</td>
                            <td>Prof. Dubois</td>
                            <td>dubois@example.com</td>
                            <td>Informatique</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('teacher', 'TCH001')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="openDeleteModal('teacher', 'TCH001', 'Prof. Dubois')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TCH002</td>
                            <td>Prof. Laurent</td>
                            <td>laurent@example.com</td>
                            <td>Mathématiques</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('teacher', 'TCH002')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick="openDeleteModal('teacher', 'TCH002', 'Prof. Laurent')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Administrateurs Tab -->
        <div class="tab-content" id="admins-tab">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Liste des Administrateurs</div>
                    <button class="btn btn-primary" id="addAdminBtn">
                        <i class="fas fa-plus"></i> Ajouter un Administrateur
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Dernière Connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ADM001</td>
                            <td>Admin Principal</td>
                            <td>admin@example.com</td>
                            <td>Super Admin</td>
                            <td>12 Nov 2023, 09:45</td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('admin', 'ADM001')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>ADM002</td>
                            <td>Gestionnaire Cours</td>
                            <td>cours.admin@example.com</td>
                            <td>Admin Cours</td>
                            <td>11 Nov 2023, 14:20</td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal('admin', 'ADM002')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal" id="addStudentModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Ajouter un Étudiant</div>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" placeholder="Entrez le prénom">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" placeholder="Entrez le nom">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" placeholder="Entrez l'email">
                </div>
                <div class="form-group">
                    <label class="form-label">Classe</label>
                    <select class="form-control">
                        <option>Sélectionnez une classe</option>
                        <option>L1 Informatique</option>
                        <option>L2 Informatique</option>
                        <option>L3 Informatique</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('addStudentModal')">Annuler</button>
                <button class="btn btn-success">Enregistrer</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="editModalTitle">Modifier l'utilisateur</div>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" value="Marie">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" value="Dupont">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="marie.dupont@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Classe / Département</label>
                    <select class="form-control">
                        <option>L1 Informatique</option>
                        <option>L2 Informatique</option>
                        <option selected>L3 Informatique</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select class="form-control">
                        <option selected>Actif</option>
                        <option>Inactif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('editUserModal')">Annuler</button>
                <button class="btn btn-success">Enregistrer les modifications</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal confirmation-modal" id="deleteConfirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Confirmation de suppression</div>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirmation-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="confirmation-text" id="deleteConfirmText">
                    Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('deleteConfirmModal')">Annuler</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Supprimer définitivement</button>
            </div>
        </div>
    </div>

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
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Add Student Modal
        document.getElementById('addStudentBtn').addEventListener('click', function() {
            openModal('addStudentModal');
        });

        // Edit User Modal
        function openEditModal(userType, userId) {
            const title = document.getElementById('editModalTitle');
            if (userType === 'student') {
                title.textContent = 'Modifier l\'étudiant';
            } else if (userType === 'teacher') {
                title.textContent = 'Modifier l\'enseignant';
            } else {
                title.textContent = 'Modifier l\'administrateur';
            }
            openModal('editUserModal');
        }

        // Delete Confirmation Modal
        function openDeleteModal(userType, userId, userName) {
            const text = document.getElementById('deleteConfirmText');
            if (userType === 'student') {
                text.textContent = `Êtes-vous sûr de vouloir supprimer l'étudiant "${userName}" ? Cette action est irréversible.`;
            } else if (userType === 'teacher') {
                text.textContent = `Êtes-vous sûr de vouloir supprimer l'enseignant "${userName}" ? Cette action est irréversible.`;
            } else {
                text.textContent = `Êtes-vous sûr de vouloir supprimer l'administrateur "${userName}" ? Cette action est irréversible.`;
            }

            // Set up the delete button
            const deleteBtn = document.getElementById('confirmDeleteBtn');
            deleteBtn.onclick = function() {
                // Here you would typically make an API call to delete the user
                alert(`Utilisateur ${userName} supprimé avec succès!`);
                closeModal('deleteConfirmModal');
                // In a real app, you would refresh the table or remove the row
            };

            openModal('deleteConfirmModal');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            }
        });
    </script>
</body>

</html>