<?php
/* ==========================================================================
   config.php — Réglages généraux du site migliore06.fr
   --------------------------------------------------------------------------
   Toutes les informations susceptibles de changer un jour (téléphone, email,
   adresse destinataire du formulaire…) sont écrites ICI, une seule fois.
   Les pages du site viennent les lire ici : pour modifier une information,
   on la change dans ce fichier et elle est mise à jour partout.

   Ce fichier est chargé au tout début de chaque page (voir index.php).
   ========================================================================== */


/* --------------------------------------------------------------------------
   Affichage des erreurs PHP
   - Sur votre poste (serveur de prévisualisation « php -S ») : les erreurs
     s'affichent à l'écran, pour pouvoir les corriger.
   - En ligne chez IONOS : elles ne sont JAMAIS montrées aux visiteurs
     (elles pourraient révéler des informations techniques utiles à un
     pirate) ; elles sont seulement enregistrées dans le journal du serveur.
   -------------------------------------------------------------------------- */

// Heure française pour tout le site (dates d'affichage des annonces…),
// quel que soit le réglage du serveur d'hébergement.
date_default_timezone_set('Europe/Paris');

// PHP_SAPI vaut 'cli-server' uniquement avec le serveur de prévisualisation.
$estEnLocal = (PHP_SAPI === 'cli-server');

error_reporting(E_ALL);                              // repérer toutes les erreurs…
ini_set('display_errors', $estEnLocal ? '1' : '0');  // …les afficher seulement en local
ini_set('log_errors', '1');                          // …et toujours les enregistrer


/* --------------------------------------------------------------------------
   Protections de sécurité envoyées au navigateur du visiteur
   Elles étaient prévues dans .htaccess, mais le serveur IONOS ne les
   applique pas (constaté le 15/10/2026) : elles sont donc envoyées ici,
   par chaque page PHP. Mêmes réglages et mêmes explications que dans
   .htaccess (section 4).
   ⚠ Tout nouveau service externe (statistiques, vidéo…) devra être ajouté
   à la ligne Content-Security-Policy, sinon le navigateur le bloquera.
   -------------------------------------------------------------------------- */

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; "
         . "frame-src https://www.google.com https://maps.google.com; form-action 'self'; "
         . "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; upgrade-insecure-requests");
    // Connexion sécurisée obligatoire pendant 1 an (HSTS) : seulement en ligne.
    if (!$estEnLocal) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}


/* --------------------------------------------------------------------------
   Coordonnées de l'entreprise (affichées dans le bandeau, le pied de page…)
   -------------------------------------------------------------------------- */

// Nom de l'entreprise, tel qu'il s'affiche en Impact bleu.
$nomEntreprise = 'Nouvelle Carrosserie Migliore';

// Téléphone tel qu'il est affiché à l'écran (avec des espaces, lisible).
$telephoneAffiche = '04 93 54 86 17';

// Même numéro au format international, sans espaces : il sert au lien
// « cliquer pour appeler » sur les téléphones portables.
$telephoneLien = '+33493548617';

// Adresse du site sur Internet (avec le « / » final). Sert aux liens
// complets lus par Google et par les réseaux sociaux (aperçu de partage).
$adresseSite = 'https://www.migliore06.fr/';

// Menu complet (Nos aménagements, Réparation, Entretien, À savoir) ou
// menu réduit (Accueil + Contact). Réduit pour la première mise en ligne
// (15/10/2026), tant que les pages aménagements n'existent pas :
// mettre true quand elles seront prêtes.
$afficherMenuComplet = false;

// Adresse email affichée sur le site.
$emailContact = 'info@migliore06.fr';

// Adresse postale du siège et de l'atelier.
$adresseRue        = '34 Boulevard Fuon Santa';
$adresseCodePostal = '06340';
$adresseVille      = 'La Trinité';


/* --------------------------------------------------------------------------
   Informations juridiques (affichées dans le pied de page et les mentions
   légales)
   -------------------------------------------------------------------------- */

$formeJuridique = 'SARL';
$capitalSocial  = '82 588,01 €';
$numeroRcs      = 'RCS Nice 326 726 619';
$numeroSiret    = '326 726 619 00017';

// Numéro de TVA intracommunautaire, au format officiel sans espaces
// (vérifié par le client sur une facture le 15/10/2026).
$numeroTva      = 'FR10326726619';


/* --------------------------------------------------------------------------
   Mentions légales
   -------------------------------------------------------------------------- */

// Directeur de la publication (obligatoirement une personne physique).
$directeurPublication = 'Bruno Carballo, co-gérant';

// Hébergeur du site.
$hebergeurNom     = 'IONOS SARL';
$hebergeurAdresse = '7 place de la Gare, 57200 Sarreguemines, France';
$hebergeurSite    = 'https://www.ionos.fr';


/* --------------------------------------------------------------------------
   Formulaire de contact
   -------------------------------------------------------------------------- */

// Adresse qui REÇOIT les messages envoyés par le formulaire de contact.
// Pour utiliser plus tard une adresse dédiée aux demandes du site,
// il suffit de la remplacer ici.
$adresseDestinataire = 'info@migliore06.fr';

// Adresse qui apparaît comme EXPÉDITEUR des messages du formulaire.
// Chez IONOS, elle doit appartenir au nom de domaine du site
// (@migliore06.fr), sinon les messages risquent d'être refusés.
// L'adresse du visiteur est placée en « Répondre à » : cliquer sur
// « Répondre » dans la messagerie lui écrit directement.
$adresseExpediteur = 'info@migliore06.fr';

// Protection anti-robots (sans captcha) :
// - délai minimum entre l'affichage du formulaire et l'envoi (un robot
//   remplit tout instantanément ; un humain met au moins quelques secondes) ;
$delaiMinimumRemplissage = 3;        // en secondes
// - délai maximum : au-delà, la page est considérée comme trop ancienne ;
$delaiMaximumRemplissage = 2 * 3600; // en secondes (2 heures)
// - nombre maximum de messages envoyés depuis une même connexion par heure.
$nombreMaxEnvoisParHeure = 3;

// Clé secrète servant à « signer » le formulaire (vérifie qu'il a bien été
// affiché par notre site). Suite de caractères aléatoires, à ne pas diffuser.
$cleSecreteFormulaire = 'de2cc8f903f9e7039ea04cc7e1a75c1f0c85a75e25217e39cf881271eec311eb';

// Dossier où sont rangés les fichiers techniques du formulaire (compteur
// anti-envois répétés, et copies des messages lors des essais sur le poste).
// Ce dossier est interdit d'accès depuis Internet (voir .htaccess).
$dossierDonnees = __DIR__ . '/donnees';


/* --------------------------------------------------------------------------
   Carrousel de la page d'accueil
   Les photos sont tirées au sort, à chaque visite, dans le stock décrit
   par contenu/carrousel.txt.
   -------------------------------------------------------------------------- */

$nombrePhotosCarrousel = 20;     // nombre de photos montrées à chaque visite
$partPhotosRecentes    = 2 / 3;  // part de photos récentes dans chaque tirage
$moisPhotoRecente      = 6;      // une photo est « récente » pendant 6 mois


/* --------------------------------------------------------------------------
   Horaires d'ouverture (affichés sur la page Contact et dans la colonne
   de droite de l'accueil, par inc/horaires.php)
   Une ligne par jour : « jour » => [ plages horaires ].
   Chaque plage s'affiche sur sa propre ligne (matin, puis après-midi).
   Jour fermé : [ 'Fermé' ].
   -------------------------------------------------------------------------- */

$horairesOuverture = [
    'Lundi'    => ['8h30 – 12h00', '14h00 – 17h30'],
    'Mardi'    => ['8h30 – 12h00', '14h00 – 17h30'],
    'Mercredi' => ['8h30 – 12h00', '14h00 – 17h30'],
    'Jeudi'    => ['8h30 – 12h00', '14h00 – 17h30'],
    'Vendredi' => ['8h30 – 12h00', '14h00 – 17h30'],
    'Samedi, dimanche et jours fériés' => ['Fermé'],
];

// Phrase affichée sous les horaires (laisser vide '' pour ne rien afficher).
$mentionHoraires = '';
