"""
preparer-mise-en-ligne.py — Prépare le dossier NCM_SiteWeb/_NCM_EnLigne/ à envoyer chez
IONOS par FileZilla.

Copie le site (NCM_SiteWeb/) SANS les éléments privés ou inutiles en ligne :
  - .git, .claude, .gitignore (outils de travail) ;
  - CLAUDE.md, cox.html et ce script lui-même (fichiers de travail) ;
  - images/photos-a-traiter, photos-retouchees (photos brutes et archives) ;
  - images/photos-pages (réservées aux futures pages, pas encore utilisées) ;
  - contenu de donnees/ (sauf son fichier de protection .htaccess) ;
  - fichiers techniques de Windows (Thumbs.db, desktop.ini) ;
  - _NCM_EnLigne et _NCM_SiteArchive eux-mêmes (copie à envoyer, sauvegarde
    de l'ancien site), _NCM_FicheGoogle (photos préparées pour la fiche Google).
  - _NCM_Versions (copies du site dans d'autres versions, pour les comparer en local).
Le dossier _NCM_EnLigne/ est entièrement recréé à chaque lancement.

Lancement (dans le dossier Z:\\NCM\\NCM_Dev\\NCM_SiteWeb) :  python preparer-mise-en-ligne.py
"""
import os
import shutil

# Ce script est rangé dans le dossier du site : la source est son propre dossier.
SOURCE = os.path.dirname(os.path.abspath(__file__))
DESTINATION = os.path.join(SOURCE, '_NCM_EnLigne')

DOSSIERS_EXCLUS = {'.git', '.claude', 'photos-a-traiter', 'photos-retouchees', 'photos-pages',
                   '_NCM_EnLigne', '_NCM_SiteArchive', '_NCM_FicheGoogle', '_NCM_Versions'}
FICHIERS_EXCLUS = {'Thumbs.db', 'desktop.ini', '.gitignore',
                   'CLAUDE.md', 'cox.html', 'preparer-mise-en-ligne.py'}

# On VIDE le dossier au lieu de le supprimer : Windows refuse de supprimer un dossier
# ouvert dans l'Explorateur ou dans WinSCP, mais accepte d'en effacer le contenu.
os.makedirs(DESTINATION, exist_ok=True)
for element in os.listdir(DESTINATION):
    chemin_element = os.path.join(DESTINATION, element)
    if os.path.isdir(chemin_element):
        shutil.rmtree(chemin_element)
    else:
        os.remove(chemin_element)

nombre, taille = 0, 0
for racine, dossiers, fichiers in os.walk(SOURCE):
    dossiers[:] = [d for d in dossiers if d not in DOSSIERS_EXCLUS]
    relatif = os.path.relpath(racine, SOURCE)
    if relatif.split(os.sep)[0] == 'donnees':
        fichiers = [f for f in fichiers if f == '.htaccess']
    for f in fichiers:
        if f in FICHIERS_EXCLUS:
            continue
        cible = os.path.join(DESTINATION, relatif)
        os.makedirs(cible, exist_ok=True)
        shutil.copy2(os.path.join(racine, f), os.path.join(cible, f))
        nombre += 1
        taille += os.path.getsize(os.path.join(racine, f))

print(f'{nombre} fichiers copiés ({taille / 1e6:.1f} Mo) dans {DESTINATION}')
