<?php
session_start();

include("../../Model/cours.php");
include("../../Model/seance.php");
include("../../Model/presence.php");
include("../../Model/inscriptionCours.php");

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
$CoursEnseignant = Cours::getCoursByEnseignant($_SESSION['id']);
$idCours = isset($_POST['courseSelect']) ? (int)$_POST['courseSelect'] : 0;
$idSeance = isset($_POST['sessionSelect']) ? (int)$_POST['sessionSelect'] : 0;
$seances = [];
if ($idCours > 0) {
    $seances = seance::getSeancesByCoursEtEnseignant($idCours, $_SESSION['id']);

    $seancesCours = seance::getSeancesWithStatsByCours($idCours);
    $etudiants = inscriptionCours::getEtudiantsByCour($idCours);
}
$stats = [
    'nbPresent' => 0,
    'nbAbsent' => 0,
    // 'nbEnAttente' => 0
];
if ($idSeance > 0) {
    $stats = Presence::getStatsBySeance($idSeance);
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - Espace Enseignant</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

        .waiting {
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

        .snackbar-success {
            visibility: hidden;
            min-width: 300px;
            max-width: 600px;
            background-color: #28a745;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px 24px;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.5s ease, top 0.5s ease;
        }

        .snackbar-success.show {
            visibility: visible;
            opacity: 1;
            top: 40px;
        }
    </style>
</head>

<body>
    <?php include("../NavBar/navbar.php") ?>


    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="TableaudeBordEnseignant.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Gestion des Présences</li>
                    </ol>
                </nav>
                <h1 class="page-title">Gestion des présences</h1>
            </div>
        </div>
        <!-- Sélecteur de cours -->
        <!-- Sélecteur de cours -->
        <div class="attendance-summary">
            <form method="post">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label for="courseSelect" class="form-label">Sélectionnez un cours</label>
                        <select class="form-select" id="courseSelect" name="courseSelect" onchange="this.form.submit()">
                            <option disabled <?= $idCours == 0 ? 'selected' : '' ?>>Choisissez un cours</option>
                            <?php foreach ($CoursEnseignant as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $idCours ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="sessionSelect" class="form-label">Sélectionnez une séance</label>
                        <select class="form-select" id="sessionSelect" name="sessionSelect" <?= empty($seances) ? 'disabled' : '' ?> onchange="this.form.submit()">
                            <option disabled <?= $idSeance == 0 ? 'selected' : '' ?>>Choisissez une séance</option>
                            <?php foreach ($seances as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($s['id'] == $idSeance) ? 'selected' : '' ?>>
                                    Séance du <?= date('d M Y', strtotime($s['date'])) ?>
                                    de <?= date('H:i', strtotime($s['heureDebut'])) ?>
                                    à <?= date('H:i', strtotime($s['heureFin'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($idSeance > 0): ?>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-around text-center pt-4 pt-md-0">
                                <div>
                                    <div class="h4 mb-0 text-success"><?= $stats['nbPresent'] ?></div>
                                    <small>Présents</small>
                                </div>
                                <div>
                                    <div class="h4 mb-0 text-danger"><?= $stats['nbAbsent'] ?></div>
                                    <small>Absents</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <form method="post" action="../../Controller/presenceController.php">
            <input type="hidden" name="id_enseignant" value="<?= htmlspecialchars($_SESSION['id'] ?? '') ?>">
            <!-- Tableau des présences -->
            <?php if ($idCours > 0 && !empty($seancesCours) && !empty($etudiants)): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Liste des présences - <?= htmlspecialchars($CoursEnseignant[array_search($idCours, array_column($CoursEnseignant, 'id'))]['titre']) ?></h5>
                        <div>
                            <span class="badge bg-success me-1">Présent</span>
                            <span class="badge bg-danger me-1">Absent</span>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height:600px; overflow:auto;">

                            <table id="attendanceTable" class="table table-modern table-bordered table-hover attendance-table">
                                <thead>
                                    <tr>
                                        <th class="student-col">Étudiant</th>
                                        <?php foreach ($seancesCours as $s): ?>
                                            <th class="session-header text-center">
                                                <?= date('d/m', strtotime($s['date'])) ?><br>

                                            </th>
                                        <?php endforeach; ?>
                                        <th class="session-header text-center">Taux</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($etudiants as $e): ?>
                                        <tr>
                                            <td class="student-col"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></td>
                                            <?php
                                            $nbPresentIndividu = 0;
                                            $statusArray = [];
                                            foreach ($seancesCours as $s):
                                                $statut = Presence::getStatutByEtudiantSeance($e['id'], $s['id']);
                                                if (!in_array($statut, ['Présent', 'Absent'])) {
                                                    $statut = 'En attente';
                                                }

                                                $classe = match ($statut) {
                                                    'Présent' => 'present',
                                                    'Absent' => 'absent',
                                                    default => 'waiting'  
                                                };
                                                if ($statut === 'Présent') $nbPresentIndividu++;
                                                $statusArray[$s['id']] = $statut;
                                            ?>
                                                <td class="text-center">
                                                    <div class="attendance-status <?= $classe ?>"
                                                        title="<?= $statut ?>"
                                                        data-date="<?= $s['date'] ?>">
                                                        <?= $statut[0] ?>
                                                    </div>
                                                    <input type="hidden"
                                                        name="status[<?= $e['id'] ?>][<?= $s['id'] ?>]"
                                                        value="<?= $statut ?>">
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center">
                                                <span class="badge bg-success"><?= count($seancesCours) > 0 ? round(($nbPresentIndividu / count($seancesCours)) * 100) : 0 ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>

                <!-- Même style de card mais avec message -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Aucune donnée disponible</h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-secondary mb-3"></i>
                        <p class="text-muted fs-5">
                            Aucun cours sélectionné.<br>
                            Veuillez choisir un cours pour afficher les présences.
                        </p>
                    </div>
                </div>

            <?php endif; ?>
            <div class="d-flex justify-content-end mb-3 mt-3">

                <div class="d-flex gap-2">
                    <button type="submit" name="save" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <input type="hidden" name="courseSelect" id="exportCourseSelect" value="<?= $idCours ?>">
                    <input type="hidden" name="sessionSelect" id="exportSessionSelect" value="<?= $idSeance ?>">

                    <input type="hidden" name="exportFormat" id="exportFormat" value="">

                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('exportFormat').value='excel'">
                        <i class="fas fa-file-excel me-1"></i> Exporter Excel
                    </button>

                    <button type="submit" class="btn btn-danger" onclick="document.getElementById('exportFormat').value='pdf'">
                        <i class="fas fa-file-pdf me-1"></i> Exporter PDF
                    </button>
                </div>
            </div>


        </form>

    </div><!-- Fin container main -->
    </div><!-- Close potentially unclosed container just in case -->
    <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Script pour changer le statut de présence au clic
        document.addEventListener('DOMContentLoaded', function() {
            const statusElements = document.querySelectorAll('.attendance-status');

            statusElements.forEach(element => {
                element.addEventListener('click', function() {
                    const hiddenInput = this.nextElementSibling;
                    let statut;
                    // if (this.classList.contains('waiting')) {
                    //     // Ne rien faire pour les "E"
                    //     return;
                    // }
                    // if (this.classList.contains('present')) {
                    //     this.classList.remove('present');
                    //     this.classList.add('absent');
                    //     this.textContent = 'A';
                    //     this.title = 'Absent';
                    //     statut = 'Absent';
                    // } else {
                    //     this.classList.remove('absent');
                    //     this.classList.add('present');
                    //     this.textContent = 'P';
                    //     this.title = 'Présent';
                    //     statut = 'Présent';
                    // }
                    const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
                    const seanceDate = this.dataset.date;

                    // Si "waiting" et séance future, ne pas cliquer
                    if (this.classList.contains('waiting') && seanceDate > today) {
                        return;
                    }

                    // Toggle Présent / Absent
                    if (this.classList.contains('present')) {
                        this.classList.remove('present');
                        this.classList.add('absent');
                        this.textContent = 'A';
                        this.title = 'Absent';
                        statut = 'Absent';
                    } else {
                        this.classList.remove('absent', 'waiting'); // en cas de "E"
                        this.classList.add('present');
                        this.textContent = 'P';
                        this.title = 'Présent';
                        statut = 'Présent';
                    }
                    if (hiddenInput) hiddenInput.value = statut;
                });
            });

        });

        $(document).ready(function() {
            $('#attendanceTable').DataTable({
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                lengthChange: false,
                pageLength: 5
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['flash_message'])): ?>
                const snackbar = document.getElementById('snackbar');
                const message = document.getElementById('snackbar-message');
                message.textContent = "<?= $_SESSION['flash_message'] ?>";
                snackbar.classList.add('show');

                setTimeout(() => {
                    snackbar.classList.remove('show');
                }, 3000);

                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
        });
    </script>

</body>

</html>