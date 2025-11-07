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
    <title>EduTrack - Tableau de Bord Administratif</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
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
            background-color: #f5f7fb;
            color: var(--dark);
            overflow-x: hidden;
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

        /* Main Content */
        .main-content {
            min-height: 100vh;
            width: 100%;
        }

        /* Content Area */
        .content {
            padding: 25px;
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

        /* Stats Cards - Version améliorée et compacte */
        .stats-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .stats-card.present {
            border-left-color: #4cc9f0;
        }

        .stats-card.absent {
            border-left-color: #f72585;
        }

        .stats-card.courses {
            border-left-color: #4361ee;
        }

        .stats-card.rate {
            border-left-color: #3a0ca3;
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .card-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .card-content {
            flex: 1;
        }

        .card-title {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 6px;
            font-weight: 500;
            line-height: 1.3;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .card-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            margin-top: auto;
        }

        .card-change.positive {
            color: #4cc9f0;
        }

        .card-change.negative {
            color: #f72585;
        }

        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-card {
                padding: 18px;
            }

            .card-value {
                font-size: 1.6rem;
            }

            .card-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 992px) {
            .content {
                padding: 20px;
            }

            .stats-card {
                padding: 16px;
            }

            .card-value {
                font-size: 1.5rem;
            }

            .card-title {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .nav-menu {
                flex-direction: column;
            }

            .nav-menu a {
                justify-content: center;
            }

            .chart-wrapper {
                height: 300px;
            }

            .stats-card {
                padding: 18px;
                margin-bottom: 15px;
            }

            .card-value {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 576px) {
            .content {
                padding: 15px;
            }

            .header {
                padding: 15px 20px;
            }

            .stats-card {
                padding: 16px;
            }

            .card-value {
                font-size: 1.5rem;
            }

            .chart-wrapper {
                height: 250px;
            }

            .card-icon {
                width: 38px;
                height: 38px;
                font-size: 1.1rem;
            }

            .card-title {
                font-size: 0.78rem;
            }

            .card-change {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 400px) {
            .stats-card {
                padding: 14px;
            }

            .card-value {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>


    <!-- Navigation -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content">


            <!-- Stats Cards - Version améliorée -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card present">
                        <div class="card-header">
                            <div class="card-icon" style="background-color: #4cc9f0;">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Présents aujourd'hui</div>
                            <div class="card-value">198</div>
                            <div class="card-change positive">
                                <i class="fas fa-arrow-up me-1"></i> 5.3% depuis hier
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card absent">
                        <div class="card-header">
                            <div class="card-icon" style="background-color: #f72585;">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Absents aujourd'hui</div>
                            <div class="card-value">27</div>
                            <div class="card-change negative">
                                <i class="fas fa-arrow-up me-1"></i> 2.1% depuis hier
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card courses">
                        <div class="card-header">
                            <div class="card-icon" style="background-color: #4361ee;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Cours actifs</div>
                            <div class="card-value">15</div>
                            <div class="card-change positive">
                                <i class="fas fa-arrow-up me-1"></i> 3 cours cette semaine
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card rate">
                        <div class="card-header">
                            <div class="card-icon" style="background-color: #3a0ca3;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Taux de présence</div>
                            <div class="card-value">88%</div>
                            <div class="card-change positive">
                                <i class="fas fa-arrow-up me-1"></i> 2.5% cette semaine
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br><br>
            <!-- Charts Section -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="chart-container">
                        <h3 class="chart-title">Évolution des inscriptions</h3>
                        <div class="chart-wrapper">
                            <canvas id="usersChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="chart-container">
                        <h3 class="chart-title">Répartition des étudiants</h3>
                        <div class="chart-wrapper">
                            <canvas id="studentsDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="chart-container">
                        <h3 class="chart-title">Taux de présence par jour (cette semaine)</h3>
                        <div class="chart-wrapper">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="chart-container">
                        <h3 class="chart-title">Statistiques par département</h3>
                        <div class="chart-wrapper">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique en courbe pour les utilisateurs
            const usersCtx = document.getElementById('usersChart').getContext('2d');

            // Créer des dégradés pour les zones
            const enseignantsGradient = usersCtx.createLinearGradient(0, 0, 0, 400);
            enseignantsGradient.addColorStop(0, 'rgba(126, 87, 194, 0.8)');
            enseignantsGradient.addColorStop(1, 'rgba(126, 87, 194, 0.1)');

            const etudiantsGradient = usersCtx.createLinearGradient(0, 0, 0, 400);
            etudiantsGradient.addColorStop(0, 'rgba(255, 152, 0, 0.8)');
            etudiantsGradient.addColorStop(1, 'rgba(255, 152, 0, 0.1)');

            const usersChart = new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                    datasets: [{
                            label: 'Enseignants',
                            data: [35, 38, 40, 42, 43, 44, 45, 45, 46, 47, 48, 49],
                            borderColor: '#7e57c2',
                            backgroundColor: enseignantsGradient,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        },
                        {
                            label: 'Étudiants',
                            data: [180, 195, 210, 225, 230, 235, 240, 242, 245, 248, 250, 252],
                            borderColor: '#ff9800',
                            backgroundColor: etudiantsGradient,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Nombre d'utilisateurs"
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });

            // Graphique en camembert pour la répartition des étudiants
            const studentsDistributionCtx = document.getElementById('studentsDistributionChart').getContext('2d');
            const studentsDistributionChart = new Chart(studentsDistributionCtx, {
                type: 'doughnut',
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
                        ],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Graphique en courbe pour le taux de présence
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');

            const attendanceGradient = attendanceCtx.createLinearGradient(0, 0, 0, 400);
            attendanceGradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
            attendanceGradient.addColorStop(1, 'rgba(54, 162, 235, 0.1)');

            const attendanceChart = new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
                    datasets: [{
                        label: 'Taux de présence (%)',
                        data: [92, 88, 95, 90, 85],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: attendanceGradient,
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 80,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Taux de présence (%)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Graphique en barres pour les départements
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            const departmentChart = new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: ['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Biologie'],
                    datasets: [{
                        label: 'Nombre d\'étudiants',
                        data: [120, 85, 65, 45, 35],
                        backgroundColor: [
                            'rgba(67, 97, 238, 0.7)',
                            'rgba(63, 55, 201, 0.7)',
                            'rgba(76, 201, 240, 0.7)',
                            'rgba(72, 149, 239, 0.7)',
                            'rgba(247, 37, 133, 0.7)'
                        ],
                        borderColor: [
                            '#4361ee',
                            '#3f37c9',
                            '#4cc9f0',
                            '#4895ef',
                            '#f72585'
                        ],
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Nombre d'étudiants"
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>