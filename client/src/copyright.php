<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos du site</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/parametre.css">
    <style>
        .about-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .about-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .top-button-container {
            margin: 20px;
        }

        .top-button {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .top-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="top-button-container">
        <button class="top-button" onclick="window.location.href='pageaccueil.php'">← Retour</button>
    </div>

    <div class="about-container">
        <h2>À propos du site</h2>
        <p>
            Ce site a été entièrement réalisé par <strong>Enzo Caillet</strong>, étudiant en deuxième année de BUT Informatique.
        </p>
        <p>
            Ce projet a été développé dans le cadre d’un stage de deux mois, effectué d’avril à mai, afin de répondre aux besoins des services municipaux.
        </p>
        <p>
            L’objectif principal de ce site est de fournir un annuaire numérique pour faciliter la gestion, la communication et l’accès aux informations entre les différents services.
        </p>
        <p style="margin-top: 20px; font-style: italic;">
            © Enzo Caillet — 2025. Tous droits réservés.
        </p>
    </div>
</body>
</html>
