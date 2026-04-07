#!/usr/bin/env python3
"""
Démarreur pour l'API Cuisine - Lido Serena
Utilisation: python start.py
"""

import subprocess
import sys
import os
from pathlib import Path

def check_python():
    """Vérifier la version de Python"""
    if sys.version_info < (3, 8):
        print("❌ Python 3.8+ est requis")
        sys.exit(1)
    print(f"✅ Python {sys.version.split()[0]} détecté")

def check_dependencies():
    """Vérifier et installer les dépendances"""
    print("\n📦 Vérification des dépendances...")
    try:
        subprocess.run(
            [sys.executable, "-m", "pip", "install", "-r", "requirements.txt", "-q"],
            check=True
        )
        print("✅ Dépendances installées/mises à jour")
    except subprocess.CalledProcessError:
        print("❌ Erreur lors de l'installation des dépendances")
        sys.exit(1)

def check_config():
    """Vérifier la configuration"""
    print("\n⚙️ Vérification de la configuration...")
    
    if not Path("conf.env").exists():
        print("❌ Le fichier conf.env est manquant!")
        sys.exit(1)
    
    # Lire et vérifier la config
    with open("conf.env", "r") as f:
        config = f.read()
        if "DB_HOST" in config:
            print("✅ Configuration trouvée")
        else:
            print("❌ Configuration invalide")
            sys.exit(1)

def check_api_file():
    """Vérifier que api.py existe"""
    if not Path("api.py").exists():
        print("❌ Le fichier api.py est manquant!")
        print("   Assurez-vous d'être dans le dossier BACK-END API")
        sys.exit(1)
    print("✅ api.py trouvé")

def main():
    print("=" * 50)
    print("  🍳 Lido Serena - API Cuisine")
    print("=" * 50)
    
    # Vérifications
    check_python()
    check_api_file()
    check_config()
    check_dependencies()
    
    print("\n" + "=" * 50)
    print("🚀 Démarrage de l'API...")
    print("=" * 50)
    print("\n📍 L'API sera disponible sur:")
    print("   🌐 http://localhost:8000")
    print("   📚 Docs: http://localhost:8000/docs")
    print("   🍳 Cuisine: http://localhost:8000/cuisine")
    print("\n⌨️  Appuyez sur Ctrl+C pour arrêter\n")
    
    try:
        subprocess.run(
            [sys.executable, "-m", "uvicorn", "api:app", "--host", "0.0.0.0", "--port", "8000", "--reload"],
            check=True
        )
    except KeyboardInterrupt:
        print("\n\n👋 Serveur arrêté!")
    except subprocess.CalledProcessError as e:
        print(f"\n❌ Erreur: {e}")
        sys.exit(1)

if __name__ == "__main__":
    # S'assurer qu'on est dans le bon répertoire
    if not Path("api.py").exists():
        print("❌ Vous devez exécuter ce script depuis le dossier BACK-END API")
        sys.exit(1)
    
    main()
