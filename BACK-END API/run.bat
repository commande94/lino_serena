@echo off
REM Démarrer l'API FastAPI pour Lido Serena

title Lido Serena - API Cuisine
color 0A

echo ========================================
echo   Lido Serena - API Cuisine
echo ========================================
echo.

REM Vérifier si on est dans le bon répertoire
if not exist "api.py" (
    echo [ERREUR] Le fichier api.py n'a pas été trouvé!
    echo Assurez-vous de lancer ce script depuis le dossier BACK-END API
    pause
    exit /b 1
)

echo [INFO] Vérification de Python...
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] Python n'est pas installé ou non disponible dans le PATH
    pause
    exit /b 1
)

echo.
echo [INFO] Installation des dépendances...
pip install -r requirements.txt --quiet
if errorlevel 1 (
    echo [ERREUR] Erreur lors de l'installation des dépendances
    pause
    exit /b 1
)

echo.
echo [INFO] Démarrage de l'API sur http://localhost:8000
echo.
echo Pour accéder à la documentation interactive: http://localhost:8000/docs
echo Pour accéder à l'interface Cuisine: http://localhost:8000/cuisine
echo.
echo Appuyez sur Ctrl+C pour arrêter le serveur
echo ========================================
echo.

REM Lancer l'API
uvicorn api:app --host 0.0.0.0 --port 8000 --reload

pause
