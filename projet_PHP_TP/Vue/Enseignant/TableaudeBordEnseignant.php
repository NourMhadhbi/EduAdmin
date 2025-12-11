<?php
session_start();
require_once("../../Model/etudiant.php");
require_once("../../Model/presence.php");
include("../../Model/seance.php");
include("../../Model/cours.php");
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
$nbEtudiantsInscrit = etudiant::nbEtudiants();
$tauxPresence = Presence::getTauxPresenceAnnuel();
$nbAbsenceSem = Presence::getWeeklyAbsence();
$seances = seance::findSeanceByEnseignant($_SESSION['id'], date('Y-m-d'));
$nbCours = Cours::getNombreCoursParEnseignant($_SESSION['id']);
date_default_timezone_set('Africa/Tunis');
$heureActuelle = date('H:i:s');
$prochaineSeances = [];
foreach ($seances as $index => $s) {
    if ($s['heureDebut'] > $heureActuelle) {
        $prochaineSeances[] = $s;
    }
}
$seancesRecentes = seance::findSeanceMoisByEnseignant($_SESSION['id']);
$AbsencesE = Presence::getAbsence();
$donneesPresence = Presence::tauxPresenceParCours();
$labels = [];
$donnees = [];

foreach ($donneesPresence as $ligne) {
    $labels[] = $ligne['cours'];
    $donnees[] = $ligne['tauxPresence'];
}

$labelsJson = json_encode($labels);
$donneesJson = json_encode($donnees);

$dateActuelle = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Présence - Espace Enseignant</title>
    <!-- Global Theme -->
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Only keeping essential overrides not covered by global-theme */
        .sidebar {
            display: none; /* Assuming sidebar is not used in global theme or handled by navbar */
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
            color: var(--gray-medium);
            font-size: 0.9rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>

<body>
    <!-- Navigation principale -->
    <?php include("../NavBar/navbar.php") ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                        <li class="breadcrumb-item active">Tableau de Bord</li>
                    </ol>
                </nav>
                <h1 class="page-title">Tableau de bord</h1>
            </div>
        </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-users"></i>
                                <div class="number"><?= $nbEtudiantsInscrit ?></div>
                                <div class="label">Étudiants inscrits</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-book-open"></i>
                                <div class="number"><?= $nbCours ?></div>
                                <div class="label">Cours </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-calendar-check"></i>
                                <div class="number"><?= $tauxPresence  ?> %</div>
                                <div class="label">Taux de présence</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <i class="fas fa-exclamation-circle"></i>
                                <div class="number"><?= $nbAbsenceSem ?></div>
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
                                <h5 class="mb-0">Séances récentes</h5>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">

                                    <table id="coursTable" class="table table-modern table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Nom du cours</th>
                                                <th>Date</th>
                                                <th>Heure</th>
                                                <th>Présents</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($seancesRecentes as $cours): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= htmlspecialchars($cours['titre']) ?></td>

                                                    <td>
                                                        <i class="fas fa-calendar-alt text-secondary me-1"></i>
                                                        <?= htmlspecialchars($cours['date']) ?>
                                                    </td>

                                                    <td>
                                                        <i class="fas fa-clock text-secondary me-1"></i>
                                                        <?= htmlspecialchars($cours['heureDebut'] . ' - ' . $cours['heureFin']) ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge bg-primary modern-badge"><?= htmlspecialchars($cours['nb_present'] . '/' . $cours['nb_total']) ?></span>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        $dateSeance = $cours['date'];
                                                        $heureDebut = $cours['heureDebut'];
                                                        $heureFin = $cours['heureFin'];

                                                        if ($dateSeance < $dateActuelle || ($dateSeance == $dateActuelle && $heureFin < $heureActuelle)) {
                                                            echo '<span class="badge bg-success modern-badge">Terminé</span>';
                                                        } elseif ($dateSeance > $dateActuelle || ($dateSeance == $dateActuelle && $heureDebut > $heureActuelle)) {
                                                            echo '<span class="badge bg-warning text-dark modern-badge">À venir</span>';
                                                        } else {
                                                            echo '<span class="badge bg-info text-dark modern-badge">En cours</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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
                                    <?php if (!empty($prochaineSeances)): ?>
                                        <?php foreach ($prochaineSeances as $seance): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($seance['titre']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= date('d M Y', strtotime($seance['date'])) ?>,
                                                        <?= substr($seance['heureDebut'], 0, 5) ?> -
                                                        <?= substr($seance['heureFin'], 0, 5) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item border-0 px-0">
                                            <div>
                                                <h6 class="mb-1">Aucun cours à venir</h6>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
                                    <?php if (!empty($AbsencesE)): ?>
                                        <?php foreach ($AbsencesE as $seanceE): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?= $seanceE['etudiant'] ?></h6>
                                                    <small class="text-danger"><?= $seanceE['nbAbsence'] ?> absences</small>
                                                </div>
                                                <p class="mb-1"><?= $seanceE['nomCours'] ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item border-0 px-0">
                                            <div>
                                                <h6 class="mb-1">Aucun étudiant</h6>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
                </div>
    </div>
    </div>

    </div>
    <?php include("../../Footer/footer.php") ?>
    <!-- Bootstrap & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        // Initialisation du graphique de présence
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            const graphiquePresence = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= $labelsJson ?>,
                    datasets: [{
                        label: 'Taux de présence (%)',
                        data: <?= $donneesJson ?>,
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
                    },
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ' : ' + context.raw + '%';
                                }
                            }
                        }
                    }
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
    <script>
        $(document).ready(function() {
            $('#coursTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 5,
                responsive: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/fr-FR.json"
                }
            });
        });
    </script>
</body>

</html>