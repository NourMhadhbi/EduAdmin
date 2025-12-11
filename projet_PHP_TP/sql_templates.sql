-- ============================================
-- SQL Templates for Creating Multiple Users
-- Edit the VALUES() to add your own users
-- ============================================

-- ============================================
-- SECTION 1: CREATE MULTIPLE STUDENTS
-- ============================================

-- Step 1: Insert students into utilisateur table
INSERT INTO utilisateur (nom, prenom, email, motDePasse, dateNaissance, adresse, telephone, photoProfil, role)
VALUES 
    -- EDIT THESE - Add your students here
    ('Ayoub', 'Kallel', 'ayoub.kallel@gmail.com', '$2y$10$placeholder', '2001-03-10', '123 Student St', '+21611111111', 'default_etudiant.png', 'etudiant'),
    ('Majd', 'Ayadi', 'majd.ayadi@gmail.com', '$2y$10$placeholder', '2002-07-15', '456 Campus Ave', '+21622222222', 'default_etudiant.png', 'etudiant'),
    ('Mahran', 'Amor', 'mahran.amor@gmail.com', '$2y$10$placeholder', '2001-11-22', '789 University Rd', '+21633333333', 'default_etudiant.png', 'etudiant'),
    ('Anouaar', 'Neifar', 'anouaar.neifar@gmail.com', '$2y$10$placeholder', '2002-05-08', '321 College Ln', '+21644444444', 'default_etudiant.png', 'etudiant'),
    ('Mariem', 'Kchaw', 'mariem.kchaw@gmail.com', '$2y$10$placeholder', '2001-09-30', '654 School Blvd', '+21655555555', 'default_etudiant.png', 'etudiant');

-- Step 2: Add them to etudiant table
INSERT INTO etudiant (id)
SELECT id FROM utilisateur 
WHERE email IN (
    'ayoub.kallel@gmail.com',
    'majd.ayadi@gmail.com',
    'mahran.amor@gmail.com',
    'anouaar.neifar@gmail.com',
    'mariem.kchaw@gmail.com'
);

-- Step 3: Set passwords using set_password.php
-- Login as admin → http://localhost/projet_PHP_TP/projet_PHP_TP/set_password.php
-- For each email above, set password to: Test123!


-- ============================================
-- SECTION 2: CREATE MULTIPLE TEACHERS
-- ============================================

-- Step 1: Insert teachers into utilisateur table
INSERT INTO utilisateur (nom, prenom, email, motDePasse, dateNaissance, adresse, telephone, photoProfil, role)
VALUES 
    -- EDIT THESE - Add your teachers here
    ('Mouna', 'Medhioub', 'mouna.medhioub@gmail.com', '$2y$10$placeholder', '1980-04-12', '100 Faculty St', '+21670000001', 'default_enseignant.png', 'enseignant'),
    ('Bassem', 'Benhamed', 'bassem.benhamed@gmail.com', '$2y$10$placeholder', '1975-08-20', '200 Professor Ave', '+21670000002', 'default_enseignant.png', 'enseignant');

-- Step 2: Add them to enseignant table
INSERT INTO enseignant (id)
SELECT id FROM utilisateur 
WHERE email IN (
    'mouna.medhioub@gmail.com',
    'bassem.benhamed@gmail.com'
);

-- Step 3: Set passwords using set_password.php


-- ============================================
-- SECTION 3: VERIFY CREATED USERS
-- ============================================

-- Check all students
SELECT u.id, u.nom, u.prenom, u.email, u.role, e.id as etudiant_id
FROM utilisateur u
JOIN etudiant e ON u.id = e.id
WHERE u.role = 'etudiant'
ORDER BY u.nom;

-- Check all teachers
SELECT u.id, u.nom, u.prenom, u.email, u.role, en.id as enseignant_id
FROM utilisateur u
JOIN enseignant en ON u.id = en.id
WHERE u.role = 'enseignant'
ORDER BY u.nom;


-- ============================================
-- SECTION 4: CREATE MULTIPLE COURSES
-- ============================================

-- Get teacher ID (replace with your teacher's email)
SET @teacher_id = (SELECT id FROM utilisateur WHERE email = 'bassem.benhamed@gmail.com');

-- Insert multiple courses
INSERT INTO cours (titre, description, level, category, image, enseignant_id)
VALUES 
    -- EDIT THESE - Add your courses here
    ('Intelligence Artificielle', 'Deep Learning, Neural Networks, Computer Vision', 'Master', 'Informatique', 'ai_course.jpg', @teacher_id),
    ('Développement Web', 'React, Node.js, MongoDB, Full Stack Development', 'Licence', 'Informatique', 'web_course.jpg', @teacher_id),
    ('Base de Données Avancées', 'SQL, NoSQL, Indexation, Optimisation', 'Licence', 'Informatique', 'database_course.jpg', @teacher_id),
    ('Algorithmes et Structures', 'Complexité, Tri, Graphes, Arbres', 'Licence', 'Informatique', 'default_course.jpg', @teacher_id),
    ('Sécurité Informatique', 'Cryptographie, Réseau, Pentesting', 'Master', 'Informatique', 'default_course.jpg', @teacher_id);

-- Verify created courses
SELECT id, titre, level, category, enseignant_id
FROM cours
ORDER BY id DESC
LIMIT 10;


-- ============================================
-- SECTION 5: ENROLL ALL STUDENTS IN ALL COURSES
-- ============================================

-- This will enroll EVERY student in EVERY course
INSERT INTO inscriptioncours (etudiant_id, cours_id)
SELECT e.id, c.id
FROM etudiant e
CROSS JOIN cours c
LEFT JOIN inscriptioncours ic ON e.id = ic.etudiant_id AND c.id = ic.cours_id
WHERE ic.etudiant_id IS NULL;  -- Only if not already enrolled

-- Verify enrollments
SELECT 
    u.nom || ' ' || u.prenom as student_name,
    c.titre as course_name,
    ic.id as enrollment_id
FROM inscriptioncours ic
JOIN utilisateur u ON ic.etudiant_id = u.id
JOIN cours c ON ic.cours_id = c.id
ORDER BY student_name, course_name;


-- ============================================
-- SECTION 6: CREATE ACTIVE SESSIONS
-- ============================================

-- Get course IDs
SELECT id, titre FROM cours ORDER BY id;

-- Method 1: Create ONE active session (2 hours) for each course
INSERT INTO seance (cours_id, date, heureDebut, heureFin)
SELECT 
    c.id,
    CURDATE(),
    DATE_SUB(NOW(), INTERVAL 5 MINUTE),
    DATE_ADD(NOW(), INTERVAL 115 MINUTE)
FROM cours c;

-- Method 2: Create sessions for specific dates
INSERT INTO seance (cours_id, date, heureDebut, heureFin)
VALUES 
    -- EDIT THESE - Add your sessions
    (1, '2025-12-11', '10:00:00', '13:00:00'),  -- Course ID 1, Dec 11, 10-13
    (1, '2025-12-11', '10:00:00', '13:00:00'),  -- Course ID 1, Dec 18, 10-13
    (2, '2025-12-11', '14:00:00', '17:00:00'),  -- Course ID 2, Dec 11, 14-17
    (2, '2025-12-11', '14:00:00', '17:00:00');  -- Course ID 2, Dec 18, 14-17

-- Verify active sessions
SELECT 
    s.id,
    c.titre as course,
    s.date,
    s.heureDebut,
    s.heureFin,
    CASE 
        WHEN s.date = CURDATE() AND s.heureDebut <= CURTIME() AND s.heureFin >= CURTIME() THEN '🟢 EN COURS'
        WHEN s.date > CURDATE() OR (s.date = CURDATE() AND s.heureDebut > CURTIME()) THEN '🟡 À VENIR'
        ELSE '🔴 TERMINÉ'
    END as status
FROM seance s
JOIN cours c ON s.cours_id = c.id
ORDER BY s.date DESC, s.heureDebut;


-- ============================================
-- SECTION 7: CLEAN UP (OPTIONAL)
-- ============================================

-- Delete test users (BE CAREFUL!)
-- DELETE FROM utilisateur WHERE email LIKE '%@student.com' OR email LIKE '%@teacher.com';

-- Delete a specific user
-- DELETE FROM utilisateur WHERE email = 'specific@email.com';

-- Delete all sessions for today
-- DELETE FROM seance WHERE date = CURDATE();


-- ============================================
-- SECTION 8: USEFUL QUERIES
-- ============================================

-- Count users by role
SELECT role, COUNT(*) as count
FROM utilisateur
GROUP BY role;

-- Count enrollments per course
SELECT c.titre, COUNT(ic.etudiant_id) as nb_students
FROM cours c
LEFT JOIN inscriptioncours ic ON c.id = ic.cours_id
GROUP BY c.id
ORDER BY nb_students DESC;

-- Count sessions per course
SELECT c.titre, COUNT(s.id) as nb_sessions
FROM cours c
LEFT JOIN seance s ON c.id = s.cours_id
GROUP BY c.id
ORDER BY nb_sessions DESC;

-- Students without enrollments
SELECT u.id, u.nom, u.prenom, u.email
FROM utilisateur u
JOIN etudiant e ON u.id = e.id
LEFT JOIN inscriptioncours ic ON e.id = ic.etudiant_id
WHERE ic.etudiant_id IS NULL;

-- Courses without sessions
SELECT c.id, c.titre
FROM cours c
LEFT JOIN seance s ON c.id = s.cours_id
WHERE s.id IS NULL;
