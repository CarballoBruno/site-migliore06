/* ==========================================================================
   main.js — Petits programmes (JavaScript) du site migliore06.fr
   --------------------------------------------------------------------------
   Le site fonctionne entièrement sans JavaScript. Ce fichier ajoute
   seulement des petits « plus » :
     1. Affichage de la carte Google Maps au clic (page Contact) ;
     2. Bouton pause / lecture du carrousel (page d'accueil) ;
     3. Trait blanc du menu sous la bonne section de l'accueil.
   ========================================================================== */


/* --------------------------------------------------------------------------
   1. CARTE GOOGLE MAPS AU CLIC (page Contact)
   La carte n'est pas chargée d'office : tant que le visiteur n'a pas
   cliqué sur « Afficher la carte », aucune donnée n'est envoyée à Google
   (respect du RGPD). Au clic, le bloc d'attente est remplacé par la carte.
   -------------------------------------------------------------------------- */

/**
 * Remplace un bloc d'attente par la carte Google Maps.
 *
 * @param {HTMLElement} emplacement  bloc d'attente (porte l'adresse de la carte
 *                                   dans son attribut data-carte-src)
 */
function afficherCarte(emplacement) {
    const carte = document.createElement('iframe');
    carte.src = emplacement.dataset.carteSrc;
    carte.title = 'Plan d\'accès : Nouvelle Carrosserie Migliore, La Trinité';
    carte.className = 'carte-google';
    carte.loading = 'lazy';
    // N'indique pas à Google depuis quelle page du site la carte est demandée.
    carte.referrerPolicy = 'no-referrer';
    emplacement.replaceWith(carte);
}

// Pour chaque bloc d'attente de la page, le bouton affiche la carte.
document.querySelectorAll('.emplacement-carte').forEach(function (emplacement) {
    const bouton = emplacement.querySelector('.bouton-carte');
    if (bouton) {
        bouton.addEventListener('click', function () {
            afficherCarte(emplacement);
        });
    }
});


/* --------------------------------------------------------------------------
   2. BOUTON PAUSE / LECTURE DU CARROUSEL (page d'accueil)
   Le défilement des photos s'arrête déjà au survol de la souris. Ce bouton
   permet aussi de l'arrêter au doigt (téléphone) ou au clavier.
   Il n'est pas affiché pour les visiteurs qui ont demandé à leur appareil
   de réduire les animations (le carrousel est alors immobile).
   -------------------------------------------------------------------------- */

/**
 * Met en pause ou relance le défilement d'un carrousel, et met à jour
 * l'apparence et la description de son bouton.
 *
 * @param {HTMLElement} carrousel  section du carrousel
 * @param {HTMLElement} bouton     bouton pause / lecture
 */
function basculerPauseCarrousel(carrousel, bouton) {
    const enPause = carrousel.classList.toggle('est-en-pause');
    bouton.setAttribute('aria-pressed', enPause ? 'true' : 'false');
    bouton.setAttribute('aria-label', enPause
        ? 'Relancer le défilement des photos'
        : 'Mettre le défilement des photos en pause');
    // Symbole affiché : « lecture » quand c'est en pause, « pause » sinon.
    bouton.querySelector('span').textContent = enPause ? '\u25B6' : '\u275A\u275A';
}

const animationsReduites = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('.carrousel').forEach(function (carrousel) {
    // Nombre de photos d'une série (la piste contient la série écrite deux
    // fois) : sert au calcul de la durée d'un tour dans css/style.css.
    const piste = carrousel.querySelector('.carrousel-piste');
    if (piste) {
        piste.style.setProperty('--nombre-photos-carrousel', piste.children.length / 2);
    }

    const bouton = carrousel.querySelector('.carrousel-bouton-pause');
    if (!bouton || animationsReduites) {
        return;
    }
    bouton.hidden = false;
    bouton.addEventListener('click', function () {
        basculerPauseCarrousel(carrousel, bouton);
    });
});


/* --------------------------------------------------------------------------
   3. TRAIT BLANC DU MENU SOUS LA BONNE SECTION DE L'ACCUEIL
   Certains liens du menu mènent à une section de la page d'accueil
   (ex. « index.php#amenagements »). Pour le serveur, c'est toujours la page
   d'accueil : il place donc le trait blanc sous la maison. Ce programme
   déplace le trait sous le lien de la section demandée, et le remet sous
   la maison quand on revient en haut de l'accueil (adresse sans « # »).
   -------------------------------------------------------------------------- */

/**
 * Place le trait blanc (lien actif) sous un lien du menu, et l'enlève des
 * autres liens.
 *
 * @param {HTMLElement[]} liens      tous les liens du menu
 * @param {HTMLElement}   lienActif  le lien à marquer
 */
function marquerLienMenu(liens, lienActif) {
    liens.forEach(function (lien) {
        const estActif = (lien === lienActif);
        lien.classList.toggle('lien-actif', estActif);
        if (estActif) {
            lien.setAttribute('aria-current', 'page');
        } else {
            lien.removeAttribute('aria-current');
        }
    });
}

const liensMenu = Array.from(document.querySelectorAll('.menu-principal-liste a'));
// Lien marqué par le serveur au chargement (la maison, sur l'accueil).
const lienMarqueParServeur = liensMenu.find(function (lien) {
    return lien.classList.contains('lien-actif');
});

/**
 * Sur la page d'accueil, marque le lien du menu qui correspond à la
 * section indiquée dans l'adresse (« #amenagements »…), ou la maison s'il
 * n'y a pas de section.
 */
function marquerLienMenuSelonSection() {
    // Uniquement sur la page d'accueil (c'est elle que le serveur a marquée
    // avec la maison).
    if (!lienMarqueParServeur || lienMarqueParServeur.getAttribute('href') !== 'index.php') {
        return;
    }
    const lienSection = liensMenu.find(function (lien) {
        return location.hash !== '' && lien.getAttribute('href') === 'index.php' + location.hash;
    });
    marquerLienMenu(liensMenu, lienSection || lienMarqueParServeur);
}

marquerLienMenuSelonSection();
// Clic sur un autre lien de section alors qu'on est déjà sur l'accueil :
// la page ne se recharge pas, seule la fin de l'adresse change.
window.addEventListener('hashchange', marquerLienMenuSelonSection);
