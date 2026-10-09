<?php
/* ==========================================================================
   inc/fonctions-contact.php — Fonctions du formulaire de contact
   --------------------------------------------------------------------------
   Toutes les opérations du formulaire de la page Contact :
     1. nettoyage et vérification de ce que le visiteur a saisi ;
     2. protections anti-robots (sans captcha) :
          - champ piège invisible (seuls les robots le remplissent) ;
          - « jeton » signé : prouve que le formulaire vient bien de notre
            site et mesure le temps de remplissage ;
          - limite du nombre d'envois par heure depuis une même connexion ;
     3. envoi du message par email (ou, sur le poste de travail, copie dans
        un fichier, car l'envoi d'emails n'y fonctionne pas) ;
     4. aide à l'affichage des erreurs dans le formulaire.

   Ce fichier est utilisé par contact.php. Il n'est jamais ouvert seul
   (le dossier inc/ est interdit d'accès depuis Internet, voir .htaccess).
   ========================================================================== */


/* --------------------------------------------------------------------------
   1. NETTOYAGE ET VÉRIFICATION DES CHAMPS
   -------------------------------------------------------------------------- */

/**
 * Nettoie un texte d'une seule ligne (nom, prénom, email…) : enlève les
 * espaces en trop, les retours à la ligne et les caractères invisibles.
 * Supprimer les retours à la ligne empêche l'« injection d'en-têtes »,
 * une technique qui détourne les formulaires pour envoyer du spam.
 *
 * @param string $texte        texte saisi par le visiteur
 * @param int    $longueurMax  nombre maximum de caractères conservés
 * @return string              texte nettoyé
 */
function nettoyerLigne(string $texte, int $longueurMax): string
{
    // Enlève tous les caractères de contrôle (dont retours à la ligne).
    $texte = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $texte) ?? '';
    // Remplace les suites d'espaces par un seul espace.
    $texte = preg_replace('/\s+/u', ' ', $texte) ?? '';
    return mb_substr(trim($texte), 0, $longueurMax);
}

/**
 * Nettoie un texte de plusieurs lignes (message, adresse postale) : garde
 * les retours à la ligne, enlève les autres caractères invisibles.
 *
 * @param string $texte        texte saisi par le visiteur
 * @param int    $longueurMax  nombre maximum de caractères conservés
 * @return string              texte nettoyé
 */
function nettoyerTexte(string $texte, int $longueurMax): string
{
    // Uniformise les retours à la ligne (Windows, Mac, Linux).
    $texte = str_replace(["\r\n", "\r"], "\n", $texte);
    // Enlève les caractères de contrôle, sauf retour à la ligne et tabulation.
    $texte = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texte) ?? '';
    return mb_substr(trim($texte), 0, $longueurMax);
}

/**
 * Lit et nettoie les champs envoyés par le formulaire.
 *
 * @param array $envoi  données reçues (en pratique $_POST)
 * @return array        champs nettoyés : nom, prenom, societe, adresse, email, telephone, message
 */
function lireChampsFormulaire(array $envoi): array
{
    return [
        'nom'     => nettoyerLigne((string) ($envoi['nom'] ?? ''), 80),
        'prenom'  => nettoyerLigne((string) ($envoi['prenom'] ?? ''), 80),
        'societe' => nettoyerLigne((string) ($envoi['societe'] ?? ''), 120),
        'adresse' => nettoyerTexte((string) ($envoi['adresse'] ?? ''), 300),
        'email'   => nettoyerLigne((string) ($envoi['email'] ?? ''), 150),
        // Téléphone facultatif : on ne garde que les chiffres, espaces,
        // « + », points, tirets et parenthèses (aucune vérification bloquante).
        'telephone' => trim(preg_replace('/[^0-9+ .\-()]/', '', nettoyerLigne((string) ($envoi['telephone'] ?? ''), 30)) ?? ''),
        'message' => nettoyerTexte((string) ($envoi['message'] ?? ''), 5000),
    ];
}

/**
 * Vérifie que les champs obligatoires sont remplis et que l'email est
 * valide.
 *
 * @param array $champs  champs nettoyés (voir lireChampsFormulaire)
 * @return array         erreurs trouvées : [nom du champ => message], vide si tout va bien
 */
function verifierChampsFormulaire(array $champs): array
{
    $erreurs = [];

    if ($champs['nom'] === '') {
        $erreurs['nom'] = 'Merci d\'indiquer votre nom.';
    }
    if ($champs['prenom'] === '') {
        $erreurs['prenom'] = 'Merci d\'indiquer votre prénom.';
    }
    if ($champs['email'] === '') {
        $erreurs['email'] = 'Merci d\'indiquer votre adresse email, pour que nous puissions vous répondre.';
    } elseif (filter_var($champs['email'], FILTER_VALIDATE_EMAIL) === false) {
        $erreurs['email'] = 'Cette adresse email ne semble pas valide (exemple : nom@entreprise.fr).';
    }
    if (mb_strlen($champs['message']) < 10) {
        $erreurs['message'] = 'Merci de décrire votre demande en quelques mots.';
    }

    return $erreurs;
}


/* --------------------------------------------------------------------------
   2. PROTECTIONS ANTI-ROBOTS
   -------------------------------------------------------------------------- */

/**
 * Crée le « jeton » placé (caché) dans le formulaire au moment où la page
 * s'affiche. Il contient l'heure d'affichage et une signature calculée avec
 * la clé secrète : personne ne peut le fabriquer ni le modifier sans elle.
 *
 * @param string $cleSecrete  clé secrète du site (config.php)
 * @return string             jeton, de la forme « heure.signature »
 */
function creerJetonFormulaire(string $cleSecrete): string
{
    $heure = (string) time();
    return $heure . '.' . hash_hmac('sha256', $heure, $cleSecrete);
}

/**
 * Vérifie le jeton renvoyé avec le formulaire.
 *
 * @param string $jeton       jeton reçu
 * @param string $cleSecrete  clé secrète du site
 * @param int    $delaiMin    délai minimum de remplissage, en secondes
 * @param int    $delaiMax    délai maximum de remplissage, en secondes
 * @return string             'ok', 'trop-rapide' ou 'invalide'
 */
function verifierJetonFormulaire(string $jeton, string $cleSecrete, int $delaiMin, int $delaiMax): string
{
    $morceaux = explode('.', $jeton);
    if (count($morceaux) !== 2 || !ctype_digit($morceaux[0])) {
        return 'invalide';
    }
    [$heure, $signature] = $morceaux;

    // hash_equals compare les signatures sans laisser deviner la bonne.
    if (!hash_equals(hash_hmac('sha256', $heure, $cleSecrete), $signature)) {
        return 'invalide';
    }

    $duree = time() - (int) $heure;
    if ($duree < $delaiMin) {
        return 'trop-rapide';
    }
    if ($duree > $delaiMax) {
        return 'invalide';
    }
    return 'ok';
}

/**
 * Calcule une « empreinte » de la connexion du visiteur (adresse IP),
 * illisible et impossible à retrouver : on peut compter ses envois sans
 * jamais enregistrer son adresse IP réelle (respect du RGPD).
 *
 * @param string $cleSecrete  clé secrète du site
 * @return string             empreinte de la connexion
 */
function calculerEmpreinteVisiteur(string $cleSecrete): string
{
    $adresseIp = (string) ($_SERVER['REMOTE_ADDR'] ?? 'inconnue');
    return hash_hmac('sha256', $adresseIp, $cleSecrete);
}

/**
 * Lit la liste des envois récents (dernière heure) enregistrés dans le
 * fichier compteur, en oubliant les plus anciens.
 *
 * @param string $fichier  chemin du fichier compteur
 * @return array           envois récents : liste de [empreinte, heure]
 */
function lireEnvoisRecents(string $fichier): array
{
    if (!is_file($fichier)) {
        return [];
    }
    $envois = json_decode((string) file_get_contents($fichier), true);
    if (!is_array($envois)) {
        return [];
    }
    $limite = time() - 3600;
    return array_values(array_filter($envois, fn($envoi) => is_array($envoi) && ($envoi[1] ?? 0) > $limite));
}

/**
 * Indique si ce visiteur a déjà atteint le nombre maximum d'envois de
 * la dernière heure.
 *
 * @param string $fichier     chemin du fichier compteur
 * @param string $empreinte   empreinte du visiteur
 * @param int    $maximum     nombre maximum d'envois par heure
 * @return bool               true si la limite est atteinte
 */
function limiteEnvoisAtteinte(string $fichier, string $empreinte, int $maximum): bool
{
    $nombre = 0;
    foreach (lireEnvoisRecents($fichier) as $envoi) {
        if ($envoi[0] === $empreinte) {
            $nombre++;
        }
    }
    return $nombre >= $maximum;
}

/**
 * Enregistre un envoi dans le fichier compteur.
 *
 * @param string $fichier    chemin du fichier compteur
 * @param string $empreinte  empreinte du visiteur
 * @return void
 */
function enregistrerEnvoi(string $fichier, string $empreinte): void
{
    $envois   = lireEnvoisRecents($fichier);
    $envois[] = [$empreinte, time()];
    // LOCK_EX : évite que deux envois simultanés abîment le fichier.
    file_put_contents($fichier, json_encode($envois), LOCK_EX);
}


/* --------------------------------------------------------------------------
   3. ENVOI DU MESSAGE
   -------------------------------------------------------------------------- */

/**
 * Compose le texte de l'email reçu par l'entreprise.
 *
 * @param array $champs  champs nettoyés et vérifiés
 * @return string        texte de l'email
 */
function composerTexteEmail(array $champs): string
{
    $lignes = [
        'Nouvelle demande reçue depuis le formulaire de contact du site migliore06.fr',
        '',
        'Nom      : ' . $champs['nom'],
        'Prénom   : ' . $champs['prenom'],
        'Société  : ' . ($champs['societe'] !== '' ? $champs['societe'] : '(non renseignée)'),
        'Email    : ' . $champs['email'],
        'Tél.     : ' . ($champs['telephone'] !== '' ? $champs['telephone'] : '(non renseigné)'),
        'Adresse  : ' . ($champs['adresse'] !== '' ? str_replace("\n", ', ', $champs['adresse']) : '(non renseignée)'),
        '',
        'Message :',
        $champs['message'],
        '',
        '—',
        'Envoyé le ' . date('d/m/Y à H:i') . '. Pour répondre, utilisez simplement « Répondre » :',
        'votre réponse partira directement vers l\'adresse email du visiteur.',
    ];
    return implode("\n", $lignes);
}

/**
 * Envoie la demande par email à l'entreprise.
 * Sur le poste de travail (serveur de prévisualisation), l'envoi d'emails
 * ne fonctionne pas : le message est alors enregistré dans un fichier
 * texte du dossier donnees/essais-emails/, pour pouvoir le relire.
 *
 * @param array  $champs        champs nettoyés et vérifiés
 * @param string $destinataire  adresse qui reçoit les demandes
 * @param string $expediteur    adresse d'expédition (domaine du site)
 * @param string $dossier       dossier des fichiers techniques
 * @param bool   $estEnLocal    true sur le poste de travail
 * @return bool                 true si l'envoi (ou l'enregistrement) a réussi
 */
function envoyerMessageContact(array $champs, string $destinataire, string $expediteur, string $dossier, bool $estEnLocal): bool
{
    $nomComplet = $champs['prenom'] . ' ' . $champs['nom'];
    $objet      = 'Demande de contact - ' . $nomComplet
                . ($champs['societe'] !== '' ? ' (' . $champs['societe'] . ')' : '');
    $texte      = composerTexteEmail($champs);

    // Sur le poste de travail : copie dans un fichier au lieu d'un email.
    if ($estEnLocal) {
        $dossierEssais = $dossier . '/essais-emails';
        if (!is_dir($dossierEssais)) {
            mkdir($dossierEssais, 0775, true);
        }
        $fichier = $dossierEssais . '/' . date('Y-m-d_H-i-s') . '.txt';
        $contenu = 'À : ' . $destinataire . "\nObjet : " . $objet . "\n\n" . $texte . "\n";
        return file_put_contents($fichier, $contenu) !== false;
    }

    // En ligne : véritable email.
    // Les en-têtes ne contiennent que des valeurs déjà nettoyées (aucun
    // retour à la ligne possible) et une adresse « Répondre à » vérifiée.
    $entetes = [
        'From: Site migliore06.fr <' . $expediteur . '>',
        'Reply-To: ' . $champs['email'],
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];

    return mail(
        $destinataire,
        mb_encode_mimeheader($objet, 'UTF-8'),
        $texte,
        implode("\r\n", $entetes),
        '-f' . $expediteur   // adresse de retour technique (exigée par certains hébergeurs)
    );
}


/* --------------------------------------------------------------------------
   4. AFFICHAGE DES ERREURS DANS LE FORMULAIRE
   -------------------------------------------------------------------------- */

/**
 * Renvoie les attributs d'accessibilité d'un champ en erreur : le champ est
 * signalé comme invalide et relié à son message d'erreur (lu par les
 * lecteurs d'écran des personnes malvoyantes).
 *
 * @param string $nomChamp  nom du champ (nom, prenom, email…)
 * @param array  $erreurs   erreurs trouvées
 * @return string           attributs à insérer, ou rien si le champ est correct
 */
function marquerChampEnErreur(string $nomChamp, array $erreurs): string
{
    if (!isset($erreurs[$nomChamp])) {
        return '';
    }
    return ' aria-invalid="true" aria-describedby="erreur-' . $nomChamp . '"';
}

/**
 * Affiche le message d'erreur d'un champ, s'il y en a un.
 *
 * @param string $nomChamp  nom du champ
 * @param array  $erreurs   erreurs trouvées
 * @return string           code HTML du message, ou rien
 */
function afficherErreurChamp(string $nomChamp, array $erreurs): string
{
    if (!isset($erreurs[$nomChamp])) {
        return '';
    }
    return '<p class="erreur-champ" id="erreur-' . $nomChamp . '">' . htmlspecialchars($erreurs[$nomChamp]) . '</p>';
}
