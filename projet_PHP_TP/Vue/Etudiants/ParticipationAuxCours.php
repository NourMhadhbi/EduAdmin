<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Pour afficher les erreurs fatales dans le navigateur
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "<b>Erreur:</b> [$errno] $errstr - $errfile:$errline<br>";
    return false; // permet le traitement normal par PHP
});
set_exception_handler(function ($exception) {
    echo "<b>Exception:</b> ", $exception->getMessage(), "<br>";
});
session_start();
include("../../Model/seance.php");
$snackbar = $_SESSION['snackbar'] ?? null;
unset($_SESSION['snackbar']);
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'etudiant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}
$idEtudiant = $_SESSION['id'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';

$seances = seance::findSeanceByEtudiant($_SESSION['id'], date('Y-m-d'));
date_default_timezone_set('Africa/Tunis');
$heureActuelle = date('H:i:s');

// On cherche l'index de la séance en cours
$indexEnCours = null;
foreach ($seances as $index => $s) {
    if ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
        $indexEnCours = $index;
        break;
    }
}

$previewSeances = [];

// Cas 1 : une séance est en cours
if ($indexEnCours !== null) {
    // On prend la séance précédente, en cours et suivante (si disponibles)

    $indices = [
        $indexEnCours - 1,
        $indexEnCours,
        $indexEnCours + 1
    ];
    foreach ($indices as $i) {
        if (isset($seances[$i])) {
            $previewSeances[] = $seances[$i];
        }
    }
}
// Cas 2 : aucune séance en cours
else {
    // On cherche la prochaine séance à venir
    $indexProchaine = null;
    foreach ($seances as $index => $s) {
        if ($s['heureDebut'] > $heureActuelle) {
            $indexProchaine = $index;
            break;
        }
    }

    if ($indexProchaine !== null) {
        // On prend la séance précédente et deux suivantes si possibles
        $indices = [
            $indexProchaine - 1,
            $indexProchaine,
            $indexProchaine + 1
        ];
        foreach ($indices as $i) {
            if (isset($seances[$i])) {
                $previewSeances[] = $seances[$i];
            }
        }
    } else {
        // Si plus de séances aujourd’hui, on prend les 3 dernières de la journée
        $previewSeances = array_slice($seances, -3);
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marquer Présence - Système de Présence Intelligente</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Css/participation.css">
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
                        <li class="breadcrumb-item active">Marquer présence</li>
                    </ol>
                </nav>

                <h1 class="page-title">Participation aux Cours</h1>
            </div>
        </div>

        <!-- Section 1: Accéder aux cours planifiés -->
        <div class="courses-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Cours Planifiés - Aujourd'hui
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Sélectionnez le cours auquel vous souhaitez marquer votre présence.
                            Seuls les cours en cours ou à venir sont disponibles.
                        </p>
                    </div>

                    <div class="course-selection">
                        <?php foreach ($previewSeances as $s): ?>
                            <?php
                            // Déterminer l’état de la séance
                            if ($s['heureFin'] < $heureActuelle) {
                                $etat = 'Terminé';
                                $icon = '<i class="fas fa-check me-1"></i>';
                                $classe = 'course-status status-finished';
                                $active = '';
                            } elseif ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
                                $etat = 'En cours';
                                $icon = '<i class="fas fa-play me-1"></i>';
                                $classe = 'course-status status-ongoing';
                                $active = 'active border border-primary shadow-sm';
                            } else {
                                $etat = 'À venir';
                                $icon = '<i class="fas fa-clock me-1"></i>';
                                $classe = 'course-status status-upcoming';
                                $active = '';
                            }
                            ?>

                            <div class="course-card <?= $active ?>" data-course-id="<?= htmlspecialchars($s['id']) ?>">
                                <div class="course-header">
                                    <div>
                                        <div class="course-title"><?= htmlspecialchars($s['titre']) ?></div>
                                        <div class="course-time">
                                            <i class="fas fa-clock"></i>
                                            <?= substr($s['heureDebut'], 0, 5) ?> - <?= substr($s['heureFin'], 0, 5) ?>
                                        </div>
                                    </div>

                                    <span class="<?= $classe ?>">
                                        <?= $icon . $etat ?>
                                    </span>
                                </div>

                                <div class="course-details">
                                    <div class="course-detail">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?>
                                    </div>
                                    <?php if (!empty($s['salle'])): ?>
                                        <div class="course-detail">
                                            <i class="fas fa-door-open"></i>
                                            <?= htmlspecialchars($s['salle']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Bloc d’action selon l’état -->
                                <?php if ($etat === 'Terminé'): ?>
                                    <div class="course-action">
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-check me-1"></i>Présence déjà marquée
                                        </button>
                                    </div>

                                <?php elseif ($etat === 'En cours'): ?>
                                    <div class="course-action">
                                        <span class="badge bg-primary">Cours sélectionné</span>
                                    </div>

                                <?php elseif ($etat === 'À venir'): ?>
                                    <div class="course-action">
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-clock me-1"></i>Indisponible
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Bouton Voir plus -->
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-primary rounded-pill px-4" id="voirPlusBtn">
                            <i class="fas fa-eye me-1"></i> Voir plus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : toutes les séances du jour -->
        <div class="modal fade" id="panelSeances" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title"><i class="fas fa-calendar-day me-2"></i>Toutes les séances d'aujourd'hui</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cours</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Professeur</th>
                                    <th>État</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seances as $s): ?>
                                    <?php
                                    if ($s['heureFin'] < $heureActuelle) {
                                        $etat = '<span class="badge bg-success">Terminé</span>';
                                    } elseif ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
                                        $etat = '<span class="badge bg-warning text-dark">En cours</span>';
                                    } else {
                                        $etat = '<span class="badge bg-info">À venir</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['titre']) ?></td>
                                        <td><?= substr($s['heureDebut'], 0, 5) ?></td>
                                        <td><?= substr($s['heureFin'], 0, 5) ?></td>
                                        <td><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></td>

                                        <td><?= $etat ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($indexEnCours) && isset($seances[$indexEnCours])): ?>
            <?php $seanceEnCours = $seances[$indexEnCours];
            if ($seanceEnCours): ?>
                <?php
                // Calcul du temps restant
                $fin = new DateTime($seanceEnCours['heureFin']);
                $now = new DateTime($heureActuelle);
                $diff = $now->diff($fin);
                $minutesRestantes = ($diff->h * 60) + $diff->i;
                ?>
                <!-- Section 2: Sélectionner le cours en cours -->
                <div class="presence-section">
                    <div class="presence-header">
                        <div class="presence-title">
                            <i class="fas fa-user-check"></i>
                            <div>
                                <div>Marquer ma présence</div>
                                <small class="text-muted">
                                    <?= htmlspecialchars($seanceEnCours['titre']) ?> - En cours
                                </small>
                            </div>
                        </div>
                        <div class="time-remaining">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="time-text">
                                    <?= $minutesRestantes ?> minute<?= $minutesRestantes > 1 ? 's' : '' ?> restantes
                                </div>
                                <div class="time-subtext">Pour marquer votre présence</div>
                            </div>
                        </div>
                    </div>





                    <form action="../../Controller/VerifyController.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="seance_id" value="<?= htmlspecialchars($seanceEnCours['id']) ?>">

                        <input type="hidden" name="etudiant_id" value="<?= htmlspecialchars($idEtudiant) ?>">

                        <div class="method-card">
                            <div class="method-header">
                                <div class="method-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <div>
                                    <div class="method-title">Téléchargement d'image</div>
                                    <div class="method-description">Importez une photo existante</div>
                                </div>
                            </div>

                            <div class="upload-container">
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="upload-text">Glissez-déposez votre image ici</div>
                                    <p class="upload-or">ou</p>

                                    <!-- Bouton visible -->
                                    <button type="button" id="browseBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-folder-open me-2"></i> Parcourir les fichiers
                                    </button>

                                    <p class="upload-info">Formats supportés : JPG, PNG (max. 5MB)</p>

                                    <!-- Input caché relié au bouton -->
                                    <input type="file" name="image" id="fileInput" accept="image/png, image/jpeg" style="display: none;" required>
                                </div>
                            </div>

                            <div class="consent-check">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consentCheck" name="consent" value="1" required>
                                    <label class="form-check-label" for="consentCheck">
                                        J'autorise l'utilisation de mon image pour la reconnaissance faciale
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-success" type="submit" id="validatePresence"
                                    <?= $minutesRestantes <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-check-circle me-2"></i> Valider ma présence
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
    </div>
<?php endif; ?>
<?php else: ?>
    <!-- Affichage pro si aucune séance en cours -->
    <div class="presence-section">
        <div class="presence-header">
            <div class="presence-title">
                <i class="fas fa-info-circle"></i>
                <div>
                    <div>Aucune séance en cours</div>
                    <small class="text-muted">
                        Il n'y a pas de cours disponible pour le marquage de présence actuellement.
                    </small>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Info :</strong> Veuillez revenir pendant l’horaire du cours pour marquer votre présence.
        </div>
    </div>
<?php endif; ?>

</div>

<!-- Pied de page -->
<?php include("../../Footer/footer.php") ?>

<!-- Snackbar -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="snackbarToast"
        class="toast align-items-center text-bg-<?= $snackbar['type'] ?? 'success' ?> border-0"
        role="alert" aria-live="assertive" aria-atomic="true"
        <?= $snackbar ? 'data-bs-autohide="true"' : '' ?>
        data-bs-delay="3500">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($snackbar['message'] ?? '') ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    </div>
</div>
<!-- Prendre photo-->


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toastEl = document.getElementById("snackbarToast");
        if (toastEl && "<?= $snackbar ? '1' : '' ?>") {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Gestion de la sélection des cours
    document.querySelectorAll('.course-card').forEach(card => {
        card.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            const status = this.querySelector('.course-status').textContent.trim();

            // Ne permettre la sélection que pour les cours disponibles
            if (status.includes('En cours') || status.includes('À venir')) {
                // Retirer la classe active de toutes les cartes
                document.querySelectorAll('.course-card').forEach(c => {
                    c.classList.remove('active');
                });

                // Ajouter la classe active à la carte cliquée
                this.classList.add('active');

                // Mettre à jour l'interface avec le cours sélectionné
                updateSelectedCourse(courseId);
            }
        });
    });
    // Quand on clique sur "Parcourir les fichiers", ça ouvre l'input caché
    document.getElementById("browseBtn").addEventListener("click", function() {
        document.getElementById("fileInput").click();
    });

    // (Optionnel) Affiche le nom du fichier sélectionné
    document.getElementById("fileInput").addEventListener("change", function() {
        if (this.files.length > 0) {
            document.querySelector(".upload-text").textContent = "Fichier sélectionné : " + this.files[0].name;
        }
    });
    document.getElementById('voirPlusBtn').addEventListener('click', () => {
        new bootstrap.Modal(document.getElementById('panelSeances')).show();
    });
</script>
</body>

</html>