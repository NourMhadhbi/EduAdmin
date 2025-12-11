<?php
session_start();
require_once("../../Model/Cours.php");

$parPage = 8;
$totalCours = Cours::countCours();
$totalPages = ceil($totalCours / $parPage);

$pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($pageActuelle - 1) * $parPage;

$coursDisponibles = Cours::getCoursPagines($parPage, $offset);

$searchCourse = $_POST['searchCourse'] ?? '';
$categoryFilter = $_POST['categoryFilter'] ?? 'all';
$parPage = 8;
$pageActuelle = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$coursDisponibles = Cours::getCoursFiltres($searchCourse, $categoryFilter, $parPage, $pageActuelle);
$totalCours = Cours::countCoursFiltres($searchCourse, $categoryFilter);
$totalPages = ceil($totalCours / $parPage);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme de Cours | EduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>






        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.05"><polygon points="1000,100 1000,0 0,100"></polygon></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-icon {
            font-size: 6rem;
            opacity: 0.8;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
        }

        .course-card {
            border: none;
            border-radius: 16px;
            transition: all 0.4s ease;
            box-shadow: var(--card-shadow);
            height: 100%;
            overflow: hidden;
            background: white;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); /* Softer shadow */
        }

        /* Green accent for Card top */
        .course-card::after { 
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #198754; /* Green */
            opacity: 0; /* Hidden by default, visible on hover? Or make it always visible if that's what user means by 'main color green' */
            transition: opacity 0.3s;
        }
        
        .course-card:hover::after {
            opacity: 1;
        }

        .course-card.selected {
            border: 2px solid var(--primary-color);
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.2);
        }

        .course-card.selected::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--success-color); /* Green for selected */
            z-index: 2;
        }

        .course-image-container {
            height: 180px;
            overflow: hidden;
            position: relative;
            background-color: #f0f0f0;
        }

        .course-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .course-card:hover .course-image {
            transform: scale(1.05);
        }

        .course-level {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .level-beginner {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .level-intermediate {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .level-advanced {
            background-color: #ffebee;
            color: #c62828;
        }

        .course-checkbox {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid #dee2e6;
            transition: all 0.2s ease;
        }

        .course-checkbox:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .footer-custom {
            background: linear-gradient(135deg, var(--dark-color), #1a1d23);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        .selection-panel {
            position: sticky;
            top: 90px;
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .selection-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .btn-affecter {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.9rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            width: 100%;
        }

        .btn-affecter:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }

        .btn-affecter:disabled {
            background: #dee2e6;
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }

        .selected-count {
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            margin-left: 0.75rem;
        }

        .filter-btn {
            border-radius: 20px;
            margin: 0 8px 12px 0;
            padding: 0.4rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            background: white;
            color: #666;
        }

        .filter-btn:hover {
            background: #f8f9fa;
        }

        .filter-btn.active {
            background: #198754; /* Green active state */
            color: white;
            border-color: #198754;
            box-shadow: 0 4px 10px rgba(25, 135, 84, 0.2);
        }

        .search-box {
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .selected-course-item {
            display: flex;
            align-items: center;
            padding: 0.85rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background: var(--gray-light);
            transition: all 0.2s ease;
            border-left: 3px solid var(--primary-color);
        }

        .selected-course-item:hover {
            background: #e8edff;
        }

        .remove-course {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .remove-course:hover {
            color: var(--warning-color);
            background: rgba(247, 37, 133, 0.1);
        }

        .course-category {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .course-duration {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .empty-selection {
            text-align: center;
            padding: 2rem 1rem;
            color: #6c757d;
        }

        .empty-selection i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 40px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .progress-indicator {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        /* ESPACEMENT AMÉLIORÉ ENTRE LES CARTES DE COURS */
        .course-card-container {
            margin-bottom: 2rem;
            /* Augmentation de l'espacement entre les cartes */
        }

        .courses-grid {
            margin-bottom: 2rem;
            /* Espacement supplémentaire sous la grille de cours */
        }

        /* Styles pour la pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            padding: 0.75rem 1rem;
            border: none;
            background-color: white;
            color: var(--dark-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .page-item:not(.active):hover .page-link {
            background-color: var(--gray-light);
            color: var(--primary-color);
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
        }

        /* Espacement amélioré */
        .courses-section {
            margin-bottom: 2rem;
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 0 2rem;
            }

            .hero-icon {
                font-size: 4rem;
                margin-bottom: 1.5rem;
            }

            .selection-panel {
                position: static;
                margin-top: 2rem;
            }

            .course-card-container {
                margin-bottom: 1.5rem;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            .page-item {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="InterfaceAccueil.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Inscription aux Cours</li>
                    </ol>
                </nav>
                <h1 class="page-title">Inscription aux Cours</h1>
            </div>
        </div>
    
            <div class="row">
            <!-- Courses Grid -->
            <div class="col-lg-8">
                <!-- Search and Filter -->
                <form method="POST" id="courseFilterForm">
                    <div class="row mb-5 filter-section">
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 search-box">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 search-box"
                                    name="searchCourse"
                                    placeholder="Rechercher un cours..."
                                    value="<?= htmlspecialchars($searchCourse) ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap">
                                <?php
                                $categories = ['all' => 'Tous', 'Développement' => 'Développement', 'Design' => 'Design', 'Business' => 'Business', 'Data Science' => 'Data Science'];
                                foreach ($categories as $key => $label):
                                    $activeClass = ($categoryFilter === $key) ? 'active' : '';
                                ?>
                                    <button class="btn filter-btn <?= $activeClass ?>" type="submit" name="categoryFilter" value="<?= $key ?>">
                                        <?= $label ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </form>


                <!-- Progress Indicator -->
                <div class="d-flex justify-content-between align-items-center mb-4 courses-section">
                    <h3 class="section-title">Cours Disponibles</h3>
                    <div class="text-muted">
                        <span id="selectedCoursesCount">0</span> sur <span id="totalCoursesCount">0</span> sélectionnés
                    </div>
                </div>
                <div class="progress-indicator">
                    <div class="progress-bar" id="selectionProgress" style="width: 0%"></div>
                </div>

                <!-- Courses Grid -->
                <div class="row mt-4 courses-grid" id="coursesGrid">
                    <?php foreach ($coursDisponibles as $c): ?>

                        <?php
                        $level = $c['level'] ?? '';

                        $levelLower = strtolower($level);
                        $levelClass = "level-" . $levelLower;

                        $levelText = match ($level) {
                            "debutant" => "Débutant",
                            "intermediaire" => "Intermédiaire",
                            "avance" => "Avancé",
                            default => "Non défini"
                        };
                        ?>

                        <div class="col-md-6 col-lg-6 course-card-container">
                            <div class="card course-card">
                                <div class="course-image-container">
<img src="../../Assets/Images/image/<?= htmlspecialchars($c['image']) ?>" class="course-image" alt="<?= htmlspecialchars($c['titre']) ?>">

                                    <div class="position-absolute top-0 end-0 m-3">
                                        <span class="course-level <?= $levelClass ?>"><?= $levelText ?></span>
                                    </div>

                                    <div class="position-absolute top-0 start-0 m-3">
                                        <input class="form-check-input course-checkbox"
                                            type="checkbox"
                                            name="cours[]"
                                            value="<?= $c['id'] ?>" data-title="<?= htmlspecialchars($c['titre']) ?>">
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold"><?= $c['titre'] ?></h5>

                                    <p class="card-text text-muted mb-3"><?= $c['description'] ?></p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- <span class="course-duration"><i class="far fa-clock me-1"></i>
                                            //$c['duree'] h
                                        </span> -->

                                        <span class="course-category"><?= $c['category'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <nav aria-label="Pagination des cours">
                        <ul class="pagination justify-content-center">

                            <!-- Bouton précédent -->
                            <li class="page-item <?= $pageActuelle <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $pageActuelle - 1 ?>">Précédent</a>
                            </li>

                            <!-- Pages -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $pageActuelle ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Bouton suivant -->
                            <li class="page-item <?= $pageActuelle >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $pageActuelle + 1 ?>">Suivant</a>
                            </li>

                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Selection Panel -->
            <div class="col-lg-4">
                <div class="selection-panel">
                    <div class="selection-header">
                        <h4 class="mb-0">Mes Sélections</h4>
                        <span class="selected-count" id="selectedCount">0</span>
                    </div>
                    <p class="text-muted mb-3">Les cours que vous sélectionnez apparaîtront ici</p>

                    <div id="selectedCourses" class="mb-4" style="max-height: 350px; overflow-y: auto;">
                        <div class="empty-selection" id="emptySelectionMessage">
                            <i class="fas fa-book-open"></i>
                            <p>Aucun cours sélectionné pour le moment</p>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary btn-affecter" id="affecterBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i>
                            Confirmer la sélection
                        </button>
                        <form id="formInscription" method="POST" action="../../Controller/inscriptionCoursController.php">
                            <input type="hidden" name="cours_ids" id="cours_ids">
                        </form>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">Vous pourrez modifier votre sélection plus tard</small>
                    </div>
                </div>


            </div>
            </div>
        </div>

    <!-- Footer -->
    <?php include("../../Footer/footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkboxes = document.querySelectorAll(".course-checkbox");
            const selectedPanelCount = document.getElementById("selectedCount");
            const selectedCoursesCount = document.getElementById("selectedCoursesCount");
            const totalCoursesCount = document.getElementById("totalCoursesCount");
            const selectedList = document.getElementById("selectedCourses");
            const emptyMsg = document.getElementById("emptySelectionMessage");
            const confirmBtn = document.getElementById("affecterBtn");

            // ----- Chargement sécurisé depuis localStorage -----
            let raw = JSON.parse(localStorage.getItem('selectedCourses') || '[]');

            let selectedCourses;

            // Si le format n'est pas un tableau de paires -> convertir
            if (raw.length > 0 && !Array.isArray(raw[0])) {
                // Ancien format (Set d'IDs) → convertir en Map avec titres récupérés du DOM
                selectedCourses = new Map();

                raw.forEach(id => {
                    const checkbox = document.querySelector(`.course-checkbox[value="${id}"]`);
                    const title = checkbox ? checkbox.dataset.title : "Cours";
                    selectedCourses.set(id, title);
                });

                // Sauvegarde dans le bon format
                localStorage.setItem('selectedCourses', JSON.stringify([...selectedCourses]));
            } else {
                // Nouveau format (paires ID / titre)
                selectedCourses = new Map(raw);
            }

            // Affiche le nombre total de cases visibles sur la page
            totalCoursesCount.textContent = checkboxes.length;

            // ----- Mise à jour du panneau -----
            function updatePanel() {
                const count = selectedCourses.size;

                selectedPanelCount.textContent = count;
                selectedCoursesCount.textContent = count;
                confirmBtn.disabled = (count === 0);

                selectedList.innerHTML = "";

                if (count === 0) {
                    emptyMsg.style.display = "block";
                    return;
                }

                emptyMsg.style.display = "none";

                selectedCourses.forEach((title, id) => {
                    const item = document.createElement("div");
                    item.className = "d-flex justify-content-between align-items-center border rounded p-2 mb-2";

                    item.innerHTML = `
                <span>${title}</span>
                <button class="btn btn-sm btn-danger remove-btn" data-id="${id}">X</button>
            `;

                    selectedList.appendChild(item);

                    // Bouton supprimer
                    item.querySelector(".remove-btn").addEventListener("click", () => {
                        selectedCourses.delete(id);

                        const checkbox = document.querySelector(`.course-checkbox[value="${id}"]`);
                        if (checkbox) checkbox.checked = false;

                        localStorage.setItem('selectedCourses', JSON.stringify([...selectedCourses]));
                        updatePanel();
                    });
                });
            }

            // ----- Gestion des checkbox -----
            checkboxes.forEach(cb => {
                if (selectedCourses.has(cb.value)) cb.checked = true;

                cb.addEventListener("change", () => {
                    if (cb.checked) {
                        selectedCourses.set(cb.value, cb.dataset.title);
                    } else {
                        selectedCourses.delete(cb.value);
                    }

                    localStorage.setItem('selectedCourses', JSON.stringify([...selectedCourses]));
                    updatePanel();
                });
            });

            updatePanel();
        });
        document.addEventListener("DOMContentLoaded", function() {
            const confirmBtn = document.getElementById("affecterBtn");
            const form = document.getElementById("formInscription");
            const inputCours = document.getElementById("cours_ids");

            confirmBtn.addEventListener("click", function() {

                const raw = JSON.parse(localStorage.getItem("selectedCourses") || "[]");
                const ids = raw.map(item => item[0]); // extraire uniquement les IDs
                console.log("IDs envoyés :", ids);
                if (ids.length === 0) {
                    alert("Sélection vide");
                    return;
                }

                // mettre les ids dans le champ caché JSON
                inputCours.value = JSON.stringify(ids);

                // soumettre le formulaire
                form.submit();
            });
        });
    </script>

    <?php if (isset($_SESSION["status"])) : ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            let status = <?= json_encode($_SESSION["status"]) ?>;

            // 🧹 Vider le localStorage ICI
            localStorage.removeItem('selectedCourses');

            if (status.success.length > 0) {
                Swal.fire({
                    icon: "success",
                    title: "Inscription réussie !",
                    text: status.success.length + " cours ajoutés."
                });
            }

            if (status.existe.length > 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Déjà inscrits",
                    text: status.existe.length + " cours déjà existants."
                });
            }

            if (status.error.length > 0) {
                Swal.fire({
                    icon: "error",
                    title: "Erreur",
                    text: status.error.join("\n")
                });
            }
        </script>

        <?php unset($_SESSION["status"]); ?>
    <?php endif; ?>





</body>

</html>