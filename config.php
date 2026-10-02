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

// PHP_SAPI vaut 'cli-server' uniquement avec le serveur de prévisualisation.
$estEnLocal = (PHP_SAPI === 'cli-server');

error_reporting(E_ALL);                              // repérer toutes les erreurs…
ini_set('display_errors', $estEnLocal ? '1' : '0');  // …les afficher seulement en local
ini_set('log_errors', '1');                          // …et toujours les enregistrer


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


/* --------------------------------------------------------------------------
   Formulaire de contact
   -------------------------------------------------------------------------- */

// Adresse qui REÇOIT les messages envoyés par le formulaire de contact.
// Pour utiliser plus tard une adresse dédiée aux demandes du site,
// il suffit de la remplacer ici.
$adresseDestinataire = 'info@migliore06.fr';
