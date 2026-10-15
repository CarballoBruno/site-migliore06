<?php
/* ==========================================================================
   inc/fonctions-contenu.php — Lecture des fichiers de contenu du site
   --------------------------------------------------------------------------
   Le contenu modifiable du site (photos du carrousel, et plus tard
   annonces « Informations importantes », actualités, réalisations) est
   rangé dans de simples fichiers texte du dossier contenu/.
   Ces fonctions lisent ces fichiers pour les afficher dans les pages :
   on met le site à jour en modifiant un fichier texte, sans toucher au code.

   Ce fichier est utilisé par les pages (index.php…). Il n'est jamais
   ouvert seul (le dossier inc/ est interdit d'accès depuis Internet).
   ========================================================================== */


/**
 * Lit le stock de photos du carrousel dans le fichier contenu/carrousel.txt.
 * Chaque ligne utile a la forme
 *     « nom-du-fichier.jpg | Légende | JJ/MM/AAAA | repère du véhicule ».
 * La date et le véhicule sont facultatifs (sans date : photo considérée
 * comme ancienne ; sans repère : chaque photo est son propre véhicule).
 * Les lignes vides, les commentaires (#) et les photos introuvables dans
 * le dossier des images sont ignorés.
 *
 * @param string $fichierListe   chemin du fichier carrousel.txt
 * @param string $dossierImages  chemin du dossier des photos (sur le serveur)
 * @return array                 photos du stock : liste de ['fichier', 'legende', 'date',
 *                                'vehicule', 'largeur', 'hauteur']
 */
function lirePhotosCarrousel(string $fichierListe, string $dossierImages): array
{
    if (!is_file($fichierListe)) {
        return [];
    }

    $photos = [];
    foreach (file($fichierListe, FILE_IGNORE_NEW_LINES) as $ligne) {
        $ligne = trim($ligne);

        // Ligne vide ou commentaire : ignorée.
        if ($ligne === '' || str_starts_with($ligne, '#')) {
            continue;
        }

        // Sépare le nom du fichier, la légende, la date et le véhicule.
        $morceaux = array_map('trim', explode('|', $ligne));
        $fichier  = $morceaux[0];
        $legende  = $morceaux[1] ?? '';
        $date     = lireDateAnnonce($morceaux[2] ?? '');
        $vehicule = ($morceaux[3] ?? '') !== '' ? $morceaux[3] : $fichier;

        // Sécurité : seul un simple nom de fichier image est accepté
        // (pas de chemin vers un autre dossier).
        if (!preg_match('/^[a-z0-9._-]+\.(jpe?g|png|webp)$/i', $fichier)) {
            continue;
        }

        // Photo introuvable (oubliée ou mal nommée) ou illisible : ignorée.
        $chemin = $dossierImages . '/' . $fichier;
        $taille = is_file($chemin) ? getimagesize($chemin) : false;
        if ($taille === false) {
            continue;
        }

        // On garde aussi la taille réelle de la photo : le navigateur réserve
        // ainsi la bonne place avant même de l'avoir chargée (indispensable
        // pour que la boucle du carrousel soit régulière).
        $photos[] = [
            'fichier'  => $fichier,
            'legende'  => $legende,
            'date'     => $date instanceof DateTimeImmutable ? $date : null,
            'vehicule' => $vehicule,
            'largeur'  => $taille[0],
            'hauteur'  => $taille[1],
        ];
    }

    return $photos;
}


/**
 * Tire au sort, dans le stock, les photos à montrer lors de cette visite :
 *   - environ $partRecentes de photos récentes (prises il y a moins de
 *     $moisRecents mois), le reste parmi les plus anciennes ; s'il en
 *     manque d'un côté, on complète avec l'autre ;
 *   - une seule photo par véhicule ;
 *   - un peu plus d'une photo de poids lourd pour deux d'utilitaires (sinon
 *     le mélange ne pourrait pas éviter trois utilitaires à la suite) : des
 *     photos d'utilitaires sont remplacées par des poids lourds, ou retirées ;
 *   - pas plus de 30 % de photos d'un même type (benne, caisse…), sinon le
 *     mélange ne pourrait pas toujours les séparer : les photos en trop sont
 *     remplacées par des photos d'un autre type.
 *
 * @param array             $stock         photos lues par lirePhotosCarrousel()
 * @param int               $nombre        nombre de photos voulu (ex. 20)
 * @param float             $partRecentes  part de photos récentes (ex. 2/3)
 * @param int               $moisRecents   âge maximal d'une photo « récente », en mois
 * @param DateTimeImmutable $maintenant    moment de la visite
 * @return array  les photos tirées (dans un ordre quelconque)
 */
function tirerPhotosCarrousel(array $stock, int $nombre, float $partRecentes, int $moisRecents, DateTimeImmutable $maintenant): array
{
    // Sépare les photos récentes des anciennes, chaque groupe mélangé.
    $limite = $maintenant->modify("-$moisRecents months");
    $recentes = array_values(array_filter($stock, fn($p) => $p['date'] !== null && $p['date'] >= $limite));
    $anciennes = array_values(array_filter($stock, fn($p) => $p['date'] === null || $p['date'] < $limite));
    shuffle($recentes);
    shuffle($anciennes);

    // Ordre de préférence : la part voulue de récentes, puis la part
    // d'anciennes, puis tout le reste (pour compléter s'il en manque).
    $nbRecentes = (int) round($nombre * $partRecentes);
    $preference = array_merge(
        array_slice($recentes, 0, $nbRecentes),
        array_slice($anciennes, 0, $nombre - $nbRecentes),
        array_slice($recentes, $nbRecentes),
        array_slice($anciennes, $nombre - $nbRecentes)
    );

    // Choix dans cet ordre, une seule photo par véhicule.
    $choisies = [];
    $restantes = [];
    $vehicules = [];
    foreach ($preference as $photo) {
        if (count($choisies) < $nombre && !isset($vehicules[$photo['vehicule']])) {
            $choisies[] = $photo;
            $vehicules[$photo['vehicule']] = true;
        } else {
            $restantes[] = $photo;
        }
    }

    // Équilibre poids lourds / utilitaires : un peu moins de 2 utilitaires
    // par poids lourd (ex. 8 poids lourds pour 12 utilitaires), pour laisser
    // de la marge au mélange (avec exactement 2 pour 1, chaque poids lourd
    // devrait être entouré d'utilitaires et le moindre écart bloquerait).
    $compter = fn($liste, $gamme) => count(array_filter($liste, fn($p) => lireGammeEtTypePhoto($p)[0] === $gamme));
    while ($compter($choisies, 'vul') > 2 * $compter($choisies, 'pl') - 2 && $compter($choisies, 'vul') > 0) {
        // Dernier utilitaire choisi (le moins prioritaire) : retiré.
        for ($i = count($choisies) - 1; lireGammeEtTypePhoto($choisies[$i])[0] !== 'vul'; $i--);
        unset($vehicules[$choisies[$i]['vehicule']]);
        array_splice($choisies, $i, 1);

        // Remplacé par un poids lourd non encore choisi, s'il en reste.
        foreach ($restantes as $j => $photo) {
            if (lireGammeEtTypePhoto($photo)[0] === 'pl' && !isset($vehicules[$photo['vehicule']])) {
                $choisies[] = $photo;
                $vehicules[$photo['vehicule']] = true;
                unset($restantes[$j]);
                break;
            }
        }
    }

    // Pas plus de 30 % d'un même type : le dernier choisi du type le plus
    // nombreux est remplacé par une photo d'un autre type, de la même gamme
    // (pour garder l'équilibre poids lourds / utilitaires).
    $maxParType = max(1, intdiv(count($choisies) * 3, 10));
    for ($essai = 0; $essai < 50; $essai++) {
        $parType = array_count_values(array_map(fn($p) => lireGammeEtTypePhoto($p)[1], $choisies));
        arsort($parType);
        $typeTrop = array_key_first($parType);
        if ($typeTrop === null || $parType[$typeTrop] <= $maxParType) {
            break;
        }
        // Photo à remplacer : en partant de la fin, la première de ce type
        // pour laquelle on trouve une remplaçante (autre type, même gamme,
        // véhicule pas encore montré).
        $remplacante = null;
        for ($i = count($choisies) - 1; $i >= 0 && $remplacante === null; $i--) {
            [$gamme, $type] = lireGammeEtTypePhoto($choisies[$i]);
            if ($type !== $typeTrop) {
                continue;
            }
            foreach ($restantes as $j => $photo) {
                [$g, $ty] = lireGammeEtTypePhoto($photo);
                if ($g === $gamme && $ty !== $typeTrop && ($parType[$ty] ?? 0) < $maxParType
                    && !isset($vehicules[$photo['vehicule']])) {
                    $remplacante = $j;
                    break;
                }
            }
        }
        if ($remplacante === null) {
            break; // stock trop petit : on garde le tirage tel quel
        }
        $i++; // la boucle ci-dessus a décompté une fois de trop
        unset($vehicules[$choisies[$i]['vehicule']]);
        $restantes[] = $choisies[$i];
        $choisies[$i] = $restantes[$remplacante];
        $vehicules[$choisies[$i]['vehicule']] = true;
        unset($restantes[$remplacante]);
    }

    return $choisies;
}


/**
 * Mélange au hasard les photos du carrousel, en évitant les répétitions :
 *   1. jamais deux photos du même type à la suite (benne, caisse…) ;
 *   2. jamais trois photos de la même gamme à la suite (pl ou vul).
 * Le type et la gamme sont lus dans le nom du fichier, de la forme
 * « gamme-type-numéro.jpg » (ex. : vul-benne-03.jpg).
 * Le carrousel tourne en boucle : la dernière photo est suivie de la
 * première, les règles sont donc aussi vérifiées à cet endroit.
 *
 * Méthode : on place les photos une à une, tirées au hasard parmi celles
 * qui respectent les règles ; si l'on se retrouve bloqué, on revient en
 * arrière pour essayer une autre photo (recherche « avec retour arrière »).
 * Si aucun ordre parfait n'est trouvé assez vite, on garde l'ordre qui
 * contient le moins de répétitions.
 *
 * @param array $photos  photos tirées par tirerPhotosCarrousel()
 * @return array         les mêmes photos, dans un ordre mélangé
 */
function melangerPhotosCarrousel(array $photos): array
{
    shuffle($photos);
    $ordre = [];
    $etapes = 0;
    if (count($photos) >= 3 && placerPhotosCarrousel($photos, $ordre, $etapes)) {
        return $ordre;
    }

    // Pas d'ordre parfait trouvé : quelques essais simples, on garde le meilleur.
    $meilleurOrdre    = $photos;
    $meilleurNbFautes = compterRepetitionsCarrousel($photos);
    for ($essai = 0; $essai < 200 && $meilleurNbFautes > 0; $essai++) {
        shuffle($photos);
        $nbFautes = compterRepetitionsCarrousel($photos);
        if ($nbFautes < $meilleurNbFautes) {
            $meilleurOrdre    = $photos;
            $meilleurNbFautes = $nbFautes;
        }
    }
    return $meilleurOrdre;
}


/**
 * Place, une à une, les photos restantes à la suite de l'ordre en cours,
 * en respectant les règles ; revient en arrière en cas de blocage.
 *
 * @param array $restantes  photos pas encore placées
 * @param array $ordre      photos déjà placées (complété au fur et à mesure)
 * @param int   $etapes     compteur d'étapes (limite le temps de recherche)
 * @return bool             true si toutes les photos ont pu être placées
 */
function placerPhotosCarrousel(array $restantes, array &$ordre, int &$etapes): bool
{
    if ($restantes === []) {
        // Toutes placées : vérifie aussi le raccord de la boucle.
        return compterRepetitionsCarrousel($ordre) === 0;
    }
    if (++$etapes > 20000) {
        return false; // trop long : on abandonne la recherche
    }

    // Les photos de la gamme la plus nombreuse parmi les restantes sont
    // essayées en premier (c'est elle qui risque de bloquer à la fin).
    $nombreParGamme = array_count_values(array_map(fn($p) => lireGammeEtTypePhoto($p)[0], $restantes));
    $candidates = array_keys($restantes);
    shuffle($candidates);
    usort($candidates, fn($a, $b) =>
        $nombreParGamme[lireGammeEtTypePhoto($restantes[$b])[0]] <=> $nombreParGamme[lireGammeEtTypePhoto($restantes[$a])[0]]);

    $typesEssayes = [];
    foreach ($candidates as $cle) {
        $photo = $restantes[$cle];
        // Deux photos de même gamme et même type mènent au même résultat :
        // inutile d'essayer la seconde.
        $sorte = implode('-', lireGammeEtTypePhoto($photo));
        if (isset($typesEssayes[$sorte]) || creeRepetitionCarrousel($ordre, $photo)) {
            continue;
        }
        $typesEssayes[$sorte] = true;

        $ordre[] = $photo;
        $suite = $restantes;
        unset($suite[$cle]);
        if (placerPhotosCarrousel($suite, $ordre, $etapes)) {
            return true;
        }
        array_pop($ordre);
    }
    return false;
}


/**
 * Indique si placer une photo à la suite des photos déjà rangées crée une
 * répétition interdite (même type que la précédente, ou 3e de la même
 * gamme à la suite).
 *
 * @param array $ordre  photos déjà rangées
 * @param array $photo  photo que l'on voudrait placer ensuite
 * @return bool         true si cela crée une répétition
 */
function creeRepetitionCarrousel(array $ordre, array $photo): bool
{
    $nombre = count($ordre);
    if ($nombre === 0) {
        return false;
    }

    [$gamme, $type] = lireGammeEtTypePhoto($photo);
    [$gammePrecedente, $typePrecedent] = lireGammeEtTypePhoto($ordre[$nombre - 1]);

    if ($type === $typePrecedent) {
        return true;
    }
    if ($nombre >= 2 && $gamme === $gammePrecedente
        && $gamme === lireGammeEtTypePhoto($ordre[$nombre - 2])[0]) {
        return true;
    }
    return false;
}


/**
 * Compte les répétitions interdites dans un ordre de photos, y compris au
 * raccord de la boucle (fin de la série suivie de son début).
 *
 * @param array $ordre  photos rangées
 * @return int          nombre de répétitions (0 = parfait)
 */
function compterRepetitionsCarrousel(array $ordre): int
{
    $nombre = count($ordre);
    $nbFautes = 0;
    for ($i = 0; $i < $nombre; $i++) {
        // Les 2 photos qui précèdent la photo n° i (en tournant en boucle).
        $precedentes = [$ordre[($i - 2 + $nombre) % $nombre], $ordre[($i - 1 + $nombre) % $nombre]];
        if ($nombre >= 3 && creeRepetitionCarrousel($precedentes, $ordre[$i])) {
            $nbFautes++;
        }
    }
    return $nbFautes;
}


/**
 * Lit la gamme et le type d'une photo dans son nom de fichier
 * (« vul-benne-03.jpg » → ['vul', 'benne']).
 *
 * @param array $photo  photo (avec sa clé 'fichier')
 * @return array        [gamme, type] ; chaînes vides si le nom ne suit pas la règle
 */
function lireGammeEtTypePhoto(array $photo): array
{
    $morceaux = explode('-', pathinfo($photo['fichier'], PATHINFO_FILENAME));
    return [$morceaux[0] ?? '', $morceaux[1] ?? ''];
}


/**
 * Lit les annonces « Informations importantes » du dossier contenu/infos/
 * et ne garde que celles à afficher aujourd'hui.
 * Chaque annonce est un fichier .txt (mode d'emploi : contenu/infos/_modele.txt) :
 *     titre: …
 *     afficher_du: JJ/MM/AAAA   (vide = dès maintenant)
 *     afficher_au: JJ/MM/AAAA   (vide = annonce permanente)
 *     ferme_du: JJ/MM/AAAA HHhMM (facultatif : fermeture exceptionnelle)
 *     ferme_au: JJ/MM/AAAA HHhMM
 *     bouton: Contactez-nous           (facultatif : lien sous l'annonce)
 *     bouton_lien: contact.php#formulaire
 *     ---
 *     texte (une ligne vide entre deux paragraphes ; « ! » au début d'un
 *     paragraphe = paragraphe mis en valeur)
 * Les fichiers dont le nom commence par « _ » (modèle) sont ignorés, ainsi
 * que les annonces dont une date est mal écrite.
 *
 * @param string            $dossier     chemin du dossier contenu/infos
 * @param DateTimeImmutable $aujourdhui  moment de la visite (date et heure)
 * @return array  annonces à afficher : liste de ['titre', 'paragraphes', 'temporaire',
 *                'fermeture', 'bouton', 'bouton_lien'] ; chaque paragraphe = ['texte', 'en_valeur'],
 *                les annonces datées (temporaires) d'abord
 */
function lireAnnoncesActives(string $dossier, DateTimeImmutable $aujourdhui): array
{
    $annonces = [];
    $fichiers = glob($dossier . '/*.txt') ?: [];
    sort($fichiers);

    foreach ($fichiers as $chemin) {
        // Modèle et annonces désactivées (« _ » au début du nom) : ignorés.
        if (str_starts_with(basename($chemin), '_')) {
            continue;
        }

        $annonce = lireFichierAnnonce($chemin);
        if ($annonce === null) {
            continue;
        }

        // Dates d'affichage : vides = pas de limite ; mal écrites = annonce ignorée.
        $debut = lireDateAnnonce($annonce['afficher_du']);
        $fin   = lireDateAnnonce($annonce['afficher_au']);
        if ($debut === false || $fin === false) {
            continue;
        }

        // Fermeture exceptionnelle sans date de fin d'affichage : l'annonce
        // disparaît d'elle-même à la fin de la fermeture.
        $finFermeture = lireDateHeureAnnonce($annonce['ferme_au'], true);
        if ($fin === null && $finFermeture instanceof DateTimeImmutable) {
            if ($aujourdhui >= $finFermeture) {
                continue;
            }
            $fin = $finFermeture;
        }
        $jour = $aujourdhui->setTime(0, 0);
        if (($debut !== null && $jour < $debut) || ($fin !== null && $jour > $fin)) {
            continue;
        }

        $annonces[] = [
            'titre'       => $annonce['titre'],
            'paragraphes' => $annonce['paragraphes'],
            'temporaire'  => $fin !== null,
            'fermeture'   => $annonce['ferme_du'] !== '',
            'bouton'      => $annonce['bouton'],
            'bouton_lien' => $annonce['bouton_lien'],
        ];
    }

    // Annonces datées (fermetures…) avant les annonces permanentes ;
    // à égalité, l'ordre alphabétique des fichiers est conservé.
    usort($annonces, fn($a, $b) => $b['temporaire'] <=> $a['temporaire']);

    return $annonces;
}


/**
 * Lit un fichier d'annonce : les lignes « clé: valeur » avant « --- »,
 * puis le texte après « --- » (paragraphes séparés par une ligne vide).
 *
 * @param string $chemin  chemin du fichier
 * @return array|null     ['titre', 'afficher_du', 'afficher_au', 'ferme_du', 'ferme_au',
 *                        'bouton', 'bouton_lien', 'paragraphes'],
 *                        ou null si le fichier est illisible ou sans titre
 */
function lireFichierAnnonce(string $chemin): ?array
{
    $lignes = file($chemin, FILE_IGNORE_NEW_LINES);
    if ($lignes === false) {
        return null;
    }

    $champs = ['titre' => '', 'afficher_du' => '', 'afficher_au' => '', 'ferme_du' => '', 'ferme_au' => '',
               'bouton' => '', 'bouton_lien' => ''];
    $texte  = [];
    $dansLeTexte = false;

    foreach ($lignes as $ligne) {
        // Retire un éventuel caractère invisible en début de fichier (BOM).
        $ligne = preg_replace('/^\xEF\xBB\xBF/', '', $ligne);

        if ($dansLeTexte) {
            $texte[] = $ligne;
            continue;
        }
        $ligne = trim($ligne);
        if ($ligne === '---') {
            $dansLeTexte = true;
        } elseif ($ligne !== '' && !str_starts_with($ligne, '#') && str_contains($ligne, ':')) {
            [$cle, $valeur] = array_map('trim', explode(':', $ligne, 2));
            $cle = strtolower($cle);
            if (array_key_exists($cle, $champs)) {
                $champs[$cle] = $valeur;
            }
        }
    }

    if ($champs['titre'] === '') {
        return null;
    }

    // Paragraphes : blocs de lignes séparés par une ligne vide. Un « ! » au
    // début d'un paragraphe le met en valeur (le « ! » n'est pas affiché).
    // Un bloc dont chaque ligne commence par « - » devient une liste à puces.
    $champs['paragraphes'] = [];
    foreach (preg_split('/\n\s*\n/', trim(implode("\n", $texte))) as $bloc) {
        $lignes = array_values(array_filter(array_map('trim', explode("\n", $bloc)), fn($ligne) => $ligne !== ''));
        $lignesAvecTiret = array_filter($lignes, fn($ligne) => str_starts_with($ligne, '-'));
        if ($lignes !== [] && count($lignesAvecTiret) === count($lignes)) {
            $champs['paragraphes'][] = [
                'texte'     => '',
                'en_valeur' => false,
                'liste'     => array_map(fn($ligne) => ltrim(substr($ligne, 1)), $lignes),
            ];
            continue;
        }
        $bloc = trim(preg_replace('/\s+/', ' ', $bloc));
        if ($bloc === '') {
            continue;
        }
        $enValeur = str_starts_with($bloc, '!');
        $champs['paragraphes'][] = [
            'texte'     => $enValeur ? ltrim(substr($bloc, 1)) : $bloc,
            'en_valeur' => $enValeur,
            'liste'     => [],
        ];
    }

    // Lien du bouton : uniquement une page du site (ex. contact.php#formulaire)
    // ou une adresse « https:// » ; sinon le bouton n'est pas affiché.
    if (!preg_match('#^(https://|[a-z0-9-]+\.php)#i', $champs['bouton_lien'])) {
        $champs['bouton'] = '';
    }

    // Typographie française : espace insécable avant « : ; ? ! » pour que
    // ces signes ne passent jamais seuls en début de ligne.
    $champs['titre'] = ajouterEspacesInsecables($champs['titre']);
    foreach ($champs['paragraphes'] as &$paragraphe) {
        $paragraphe['texte'] = ajouterEspacesInsecables($paragraphe['texte']);
        $paragraphe['liste'] = array_map('ajouterEspacesInsecables', $paragraphe['liste']);
    }
    unset($paragraphe);

    return $champs;
}


/**
 * Transforme une date écrite « JJ/MM/AAAA » en date utilisable.
 *
 * @param string $texte  date écrite dans le fichier d'annonce (peut être vide)
 * @return DateTimeImmutable|null|false  la date ; null si vide ; false si mal écrite
 */
function lireDateAnnonce(string $texte): DateTimeImmutable|null|false
{
    if ($texte === '') {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!d/m/Y', $texte);
    // Refuse aussi les dates impossibles (ex. 31/02/2027, que PHP corrigerait).
    if ($date === false || $date->format('d/m/Y') !== $texte) {
        return false;
    }
    return $date;
}


/**
 * Lit les fermetures exceptionnelles (route coupée, coupure de courant…)
 * indiquées dans les annonces de contenu/infos/ par « ferme_du » et
 * « ferme_au ». Elles servent à afficher « Fermé exceptionnellement ».
 *
 * @param string $dossier  chemin du dossier contenu/infos
 * @return array  liste de ['debut' => DateTimeImmutable, 'fin' => DateTimeImmutable]
 */
function lireFermeturesExceptionnelles(string $dossier): array
{
    $fermetures = [];
    foreach (glob($dossier . '/*.txt') ?: [] as $chemin) {
        if (str_starts_with(basename($chemin), '_')) {
            continue;
        }
        $annonce = lireFichierAnnonce($chemin);
        if ($annonce === null || $annonce['ferme_du'] === '' || $annonce['ferme_au'] === '') {
            continue;
        }
        $debut = lireDateHeureAnnonce($annonce['ferme_du'], false);
        $fin   = lireDateHeureAnnonce($annonce['ferme_au'], true);
        if ($debut instanceof DateTimeImmutable && $fin instanceof DateTimeImmutable && $debut < $fin) {
            $fermetures[] = ['debut' => $debut, 'fin' => $fin];
        }
    }
    return $fermetures;
}


/**
 * Transforme « JJ/MM/AAAA HHhMM » (ou « JJ/MM/AAAA HH:MM ») en date et
 * heure. Sans heure : début de journée, ou fin de journée si $finDeJournee.
 *
 * @param string $texte         date et heure écrites dans le fichier (peut être vide)
 * @param bool   $finDeJournee  sans heure, prendre la fin de la journée ?
 * @return DateTimeImmutable|null|false  la date ; null si vide ; false si mal écrite
 */
function lireDateHeureAnnonce(string $texte, bool $finDeJournee): DateTimeImmutable|null|false
{
    $texte = trim($texte);
    if ($texte === '') {
        return null;
    }
    if (!preg_match('#^(\d{2}/\d{2}/\d{4})(?:\s+(\d{1,2})\s*[h:]\s*(\d{2})?)?$#i', $texte, $m)) {
        return false;
    }
    $date = lireDateAnnonce($m[1]);
    if (!$date instanceof DateTimeImmutable) {
        return false;
    }
    if (isset($m[2]) && $m[2] !== '') {
        return $date->setTime((int) $m[2], (int) ($m[3] ?? 0));
    }
    return $finDeJournee ? $date->modify('+1 day') : $date;
}


/**
 * Lit les cartes « Nos aménagements » dans le fichier contenu/amenagements.txt.
 * Chaque ligne utile a la forme « Titre | photo.jpg | Texte | page.php », suivie
 * éventuellement de « | Mention | logo1.png, logo2.png | https://lien1, https://lien2 ».
 * Un logo n'est gardé que s'il existe dans images/logos/ et si son lien
 * (même rang dans la liste) commence par https://.
 * La page n'est gardée que si elle existe vraiment dans le site (sinon la
 * carte est affichée sans lien). Une carte dont la photo est introuvable
 * n'est pas affichée.
 *
 * @param string $fichierListe   chemin du fichier amenagements.txt
 * @param string $dossierImages  chemin du dossier des photos (images/carrousel)
 * @param string $dossierSite    chemin du dossier du site (pour vérifier les pages)
 * @return array  cartes : liste de ['titre', 'photo', 'texte', 'page', 'largeur', 'hauteur', 'partenaire',
 *                'description_photo'] ;
 *                'partenaire' = ['mention', 'logos' => liste de ['logo', 'lien', 'format']] ou null
 */
function lireCartesAmenagements(string $fichierListe, string $dossierImages, string $dossierSite): array
{
    if (!is_file($fichierListe)) {
        return [];
    }

    $cartes = [];
    foreach (file($fichierListe, FILE_IGNORE_NEW_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#')) {
            continue;
        }
        [$titre, $photo, $texte, $page, $mention, $logo, $lien, $descriptionPhoto] = array_pad(array_map('trim', explode('|', $ligne)), 8, '');

        // Sécurité : simple nom de fichier uniquement (pas de chemin).
        if ($titre === '' || !preg_match('/^[a-z0-9._-]+\.(jpe?g|png|webp)$/i', $photo)) {
            continue;
        }
        $taille = is_file($dossierImages . '/' . $photo) ? getimagesize($dossierImages . '/' . $photo) : false;
        if ($taille === false) {
            continue;
        }
        if (!preg_match('/^[a-z0-9-]+\.php$/', $page) || !is_file($dossierSite . '/' . $page)) {
            $page = '';
        }

        // Partenaires (facultatif) : chaque logo doit exister. Son lien doit
        // être sûr (https://) ; un tiret « - » = logo affiché sans lien
        // (ex. notre propre logo NCM).
        $logos = [];
        $liens = array_map('trim', explode(',', $lien));
        foreach (array_map('trim', explode(',', $logo)) as $rang => $unLogo) {
            $unLien = $liens[$rang] ?? '';
            if ($unLien === '-') {
                $unLien = '';
            } elseif (!str_starts_with($unLien, 'https://')) {
                continue;
            }
            if (preg_match('/^[a-z0-9._-]+\.(png|svg|webp|jpe?g)$/i', $unLogo)
                && is_file($dossierSite . '/images/logos/' . $unLogo)) {
                $logos[] = [
                    'logo'   => $unLogo,
                    'lien'   => $unLien,
                    'format' => lireFormatLogo($dossierSite . '/images/logos/' . $unLogo),
                ];
            }
        }
        $partenaire = $logos === [] ? null : ['mention' => $mention, 'logos' => $logos];

        $cartes[] = [
            'titre'      => $titre,
            'photo'      => $photo,
            'texte'      => $texte,
            'page'       => $page,
            'largeur'    => $taille[0],
            'hauteur'    => $taille[1],
            'partenaire' => $partenaire,
            'description_photo' => $descriptionPhoto,
        ];
    }
    return $cartes;
}


/**
 * Indique la forme d'un logo, pour que tous les logos paraissent de même
 * importance à l'écran : à hauteur égale, un logo carré (HMF, JPM) paraît
 * bien plus petit qu'un logo très allongé (Palkit, Dhollandia). Les logos
 * carrés ou hauts sont donc affichés plus grands (voir style.css).
 *
 * @param string $cheminLogo  chemin du fichier du logo (png, jpg, webp ou svg)
 * @return string  'allonge' (au moins 2 fois plus large que haut),
 *                 'moyen' (de 1,3 à 2 fois plus large que haut)
 *                 ou 'carre' (presque carré, ou plus haut que large)
 */
function lireFormatLogo(string $cheminLogo): string
{
    $largeur = 0;
    $hauteur = 0;
    if (str_ends_with(strtolower($cheminLogo), '.svg')) {
        // SVG : les dimensions sont dans l'attribut viewBox="0 0 largeur hauteur".
        if (preg_match('/viewBox="[\d.\s-]*?([\d.]+)\s+([\d.]+)"/', (string) file_get_contents($cheminLogo), $resultat)) {
            [$largeur, $hauteur] = [(float) $resultat[1], (float) $resultat[2]];
        }
    } else {
        $taille = @getimagesize($cheminLogo);
        if ($taille !== false) {
            [$largeur, $hauteur] = [$taille[0], $taille[1]];
        }
    }
    if ($hauteur <= 0) {
        return 'allonge';   // dimensions illisibles : taille normale
    }
    $rapport = $largeur / $hauteur;
    if ($rapport >= 2) {
        return 'allonge';
    }
    return $rapport >= 1.3 ? 'moyen' : 'carre';
}


/**
 * Remplace l'espace ordinaire placée avant « : ; ? ! » (et après « « »,
 * avant « » ») par une espace insécable : le signe reste collé au mot qui
 * le précède et ne passe jamais seul à la ligne (règle de typographie
 * française). Si l'espace manque, elle est ajoutée.
 *
 * @param string $texte  texte d'origine
 * @return string        texte corrigé
 */
function ajouterEspacesInsecables(string $texte): string
{
    $texte = preg_replace('/\s*([:;?!»])/u', "\u{00A0}$1", $texte);
    $texte = preg_replace('/«\s*/u', "«\u{00A0}", $texte);
    // Exception : les adresses « https:// » ne sont pas touchées.
    return str_replace("\u{00A0}://", '://', $texte);
}
