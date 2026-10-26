/* ==========================================================================
   main.js — Petits programmes (JavaScript) du site migliore06.fr
   --------------------------------------------------------------------------
   Le site fonctionne entièrement sans JavaScript. Ce fichier ajoute
   seulement des petits « plus » :
     1. Affichage de la carte Google Maps au clic (page Contact) ;
     2. Bouton pause / lecture du carrousel (page d'accueil) ;
     3. Trait blanc du menu sous la bonne section de l'accueil ;
     4. Ouverture du menu déroulant « Nos aménagements » au toucher / clic.
     5. Annonces « Informations importantes » ouvertes par-dessus la page.
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

// Liens du menu (y compris le bouton du menu déroulant, qui reçoit le trait
// quand on clique sur un aménagement).
const liensMenu = Array.from(document.querySelectorAll('.menu-principal-liste a, .menu-deroulant-bouton'));
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
    const liensSection = liensMenu.filter(function (lien) {
        return location.hash !== '' && lien.getAttribute('href') === 'index.php' + location.hash;
    });
    if (location.hash === '') {
        marquerLienMenu(liensMenu, lienMarqueParServeur);
    } else if (liensSection.length === 1) {
        marquerLienMenu(liensMenu, liensSection[0]);
    }
    // Plusieurs liens mènent à la même section (ex. les aménagements dont la
    // page n'existe pas encore mènent tous à « #amenagements ») : on ne sait
    // pas lequel a été cliqué ici, c'est le clic (ci-dessous) qui le marque.
}

marquerLienMenuSelonSection();
// Clic sur un autre lien de section alors qu'on est déjà sur l'accueil :
// la page ne se recharge pas, seule la fin de l'adresse change.
window.addEventListener('hashchange', marquerLienMenuSelonSection);

// Clic sur un lien de section de l'accueil : le trait blanc va sous le lien
// cliqué (utile quand plusieurs liens mènent à la même section). Pour un
// aménagement du menu déroulant, c'est le bouton « Nos aménagements » qui
// est souligné.
liensMenu.forEach(function (lien) {
    lien.addEventListener('click', function () {
        const adresse = lien.getAttribute('href') || '';
        if (lienMarqueParServeur && lienMarqueParServeur.getAttribute('href') === 'index.php'
            && adresse.startsWith('index.php#')) {
            const deroulant = lien.closest('.menu-deroulant');
            marquerLienMenu(liensMenu, deroulant ? deroulant.querySelector('.menu-deroulant-bouton') : lien);
        }
    });
});


/* --------------------------------------------------------------------------
   4. MENU DÉROULANT « NOS AMÉNAGEMENTS »
   Sur ordinateur, la liste s'ouvre au simple survol de la souris (géré
   par style.css). Ce programme permet aussi de l'ouvrir et de la fermer
   en touchant ou en cliquant sur le bouton (téléphone, tablette, clavier),
   et la referme quand on touche ailleurs ou qu'on appuie sur Échap.
   -------------------------------------------------------------------------- */

const boutonDeroulant = document.querySelector('.menu-deroulant-bouton');

/**
 * Ouvre ou ferme la liste du menu déroulant.
 *
 * @param {boolean} ouvrir  true pour ouvrir, false pour fermer
 */
function basculerMenuDeroulant(ouvrir) {
    boutonDeroulant.setAttribute('aria-expanded', ouvrir ? 'true' : 'false');
}

if (boutonDeroulant) {
    boutonDeroulant.addEventListener('click', function () {
        basculerMenuDeroulant(boutonDeroulant.getAttribute('aria-expanded') !== 'true');
    });
    // Toucher ou clic en dehors du menu déroulant : on le referme.
    document.addEventListener('click', function (evenement) {
        if (!evenement.target.closest('.menu-deroulant')) {
            basculerMenuDeroulant(false);
        }
    });
    // Touche Échap : on le referme et on revient sur le bouton.
    document.addEventListener('keydown', function (evenement) {
        if (evenement.key === 'Escape' && boutonDeroulant.getAttribute('aria-expanded') === 'true') {
            basculerMenuDeroulant(false);
            boutonDeroulant.focus();
        }
    });
}


/* --------------------------------------------------------------------------
   5. ANNONCES « INFORMATIONS IMPORTANTES » PAR-DESSUS LA PAGE (accueil)
   Version 8003 (26/10/2026, demande du client) : dans la colonne de droite,
   seul le titre de chaque annonce est affiché ; un clic ouvre l'annonce
   complète dans un encart qui vient par-dessus la page (élément <dialog>).
   On le ferme avec la croix, la touche Échap ou un clic à côté de l'encart.
   Sans JavaScript, ce code ne s'exécute pas : les boutons restent cachés et
   les annonces restent affichées en entier (voir style.css, partie 9).
   -------------------------------------------------------------------------- */

document.documentElement.classList.add('avec-javascript');

document.querySelectorAll('.annonce-ouvrir').forEach(function (boutonOuvrir) {
    const encart = document.getElementById(boutonOuvrir.getAttribute('aria-controls'));
    if (!encart || typeof encart.showModal !== 'function') {
        return;   // navigateur trop ancien : l'annonce reste affichée en entier
    }
    const boutonFermer = encart.querySelector('.annonce-fermer');

    boutonOuvrir.hidden = false;
    if (boutonFermer) {
        boutonFermer.hidden = false;
        boutonFermer.addEventListener('click', function () {
            encart.close();
        });
    }

    boutonOuvrir.addEventListener('click', function () {
        encart.showModal();
    });

    // Clic à côté de l'encart (sur le fond assombri) : on le ferme.
    encart.addEventListener('click', function (evenement) {
        if (evenement.target === encart) {
            encart.close();
        }
    });
});
