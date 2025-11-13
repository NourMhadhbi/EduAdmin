<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation - Plateforme Présence Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
        }
        
        body {
             background: 
                linear-gradient(rgba(114, 114, 114, 0.4), rgba(152, 152, 152, 0.32)),
                url('education.jpg') center/cover no-repeat;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .reset-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }
        
        .reset-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .reset-header {
            background: linear-gradient(135deg, #b6a794ff, #cfa876);
            color: white;
            padding: 15px 10px;
            text-align: center;
            position: relative;
        }
        
        .reset-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
           
        }
        
        .reset-header i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }
        .reset-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }
        
        .reset-header p {
           opacity: 0.9;
            font-size: 0.9rem;
        }
        .reset-body {
            padding: 30px;
        }
        
        .btn-reset {
            background: #b6a794ff;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            width: 100%;
            margin-top: 10px;
        }
        
        .reset-footer {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .reset-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
           <i class="fas fa-key" style="font-size:50px;"></i>
                <h2>Réinitialiser le mot de passe</h2>
                <p class="mb-0">Entrez votre email pour recevoir un lien de réinitialisation</p>
            </div>
            
            <div class="reset-body">
                <form id="resetForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    
                    <button type="submit" class="btn btn-reset">Envoyer le lien de réinitialisation</button>
                </form>
                
                <div class="reset-footer">
                    <p><a href="index.html">Retour à la connexion</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Un lien de réinitialisation a été envoyé à votre adresse email.');
            window.location.href = 'index.html';
        });
    </script>
</body>
</html>