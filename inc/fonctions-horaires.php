<?php
/* ==========================================================================
   inc/fonctions-horaires.php — La carrosserie est-elle ouverte en ce moment ?
   --------------------------------------------------------------------------
   Calcule, à partir des horaires de config.php ($horairesOuverture), des
   jours fériés et des fermetures exceptionnelles (fichiers de
   contenu/infos/), si la carrosserie est ouverte au moment où le visiteur
   regarde la page, et prépare la phrase affichée :
       « Actuellement ouvert jusqu'à 12h00 »
       « Fermé jusqu'à 14h00 »  (réouverture le jour même)
       « Fermé jusqu'à demain 8h30 »
       « Fermé exceptionnellement jusqu'à lundi 12 octobre 8h30 »

   Ce fichier est utilisé par la page d'accueil. Il n'est jamais ouvert
   seul (le dossier inc/ est interdit d'accès depuis Internet).
   ========================================================================== */


/**
 * Calcule l'état d'ouverture de la carrosserie à un moment donné.
 *
 * @param array             $horaires    horaires de config.php (« jour » => [plages])
 * @param array             $fermetures  fermetures exceptionnelles : liste de ['debut', 'fin']
 * @param DateTimeImmutable $maintenant  moment de la visite
 * @return array  ['ouvert' => bool, 'exceptionnel' => bool, 'message' => phrase à afficher]
 */
function calculerEtatOuverture(array $horaires, array $fermetures, DateTimeImmutable $maintenant): array
{
    // Ouvert en ce moment : jusqu'à quelle heure ?
    $plage = trouverPlageEnCours($horaires, $maintenant);
    if ($plage !== null && trouverFermetureEnCours($fermetures, $maintenant) === null) {
        $fin = $plage['fin'];
        // Une fermeture exceptionnelle peut commencer avant la fin de la plage.
        foreach ($fermetures as $fermeture) {
            if ($fermeture['debut'] > $maintenant && $fermeture['debut'] < $fin) {
                $fin = $fermeture['debut'];
            }
        }
        return [
            'ouvert'       => true,
            'exceptionnel' => false,
            'message'      => 'Actuellement ouvert jusqu\'à ' . ecrireHeure($fin),
        ];
    }

    // Fermé en ce moment : pour quelle raison, et quand rouvre-t-on ?
    $exceptionnel = trouverFermetureEnCours($fermetures, $maintenant) !== null;
    $reouverture  = trouverProchaineOuverture($horaires, $fermetures, $maintenant);

    $message = $exceptionnel ? 'Fermé exceptionnellement' : 'Fermé';
    if ($reouverture !== null) {
        // « jusqu'à 14h00 » le jour même, sinon « jusqu'à demain 8h30 »…
        $jour = ecrireJourRelatif($reouverture, $maintenant);
        $message .= ' jusqu\'à ' . ($jour === 'aujourd\'hui' ? '' : $jour . ' ')
                  . ecrireHeure($reouverture);
    }

    return [
        'ouvert'       => false,
        'exceptionnel' => $exceptionnel,
        'message'      => $message,
    ];
}


/**
 * Donne les plages d'ouverture d'un jour précis, sous forme d'heures de
 * début et de fin. Un jour férié ou un jour « Fermé » n'a aucune plage.
 *
 * @param array             $horaires  horaires de config.php
 * @param DateTimeImmutable $jour      le jour voulu
 * @return array  liste de ['debut' => DateTimeImmutable, 'fin' => DateTimeImmutable]
 */
function lirePlagesDuJour(array $horaires, DateTimeImmutable $jour): array
{
    if (estJourFerie($jour)) {
        return [];
    }

    // Nom du jour (« lundi »…) et ligne correspondante des horaires :
    // « Lundi » ou une ligne qui le contient (« Samedi, dimanche et… »).
    $nomDuJour = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][(int) $jour->format('w')];
    $plagesEcrites = [];
    foreach ($horaires as $jours => $plages) {
        if (str_contains(mb_strtolower($jours), $nomDuJour)) {
            $plagesEcrites = $plages;
            break;
        }
    }

    // « 8h30 – 12h00 » → heure de début et heure de fin ce jour-là
    // (« Fermé » ne correspond pas et donne donc aucune plage).
    $plagesDuJour = [];
    foreach ($plagesEcrites as $plage) {
        if (preg_match('/(\d{1,2})h(\d{2})\s*[–-]\s*(\d{1,2})h(\d{2})/u', $plage, $m)) {
            $plagesDuJour[] = [
                'debut' => $jour->setTime((int) $m[1], (int) $m[2]),
                'fin'   => $jour->setTime((int) $m[3], (int) $m[4]),
            ];
        }
    }
    return $plagesDuJour;
}


/**
 * Cherche la plage d'ouverture en cours à un moment donné.
 *
 * @param array             $horaires  horaires de config.php
 * @param DateTimeImmutable $moment    moment voulu
 * @return array|null  la plage ['debut', 'fin'], ou null si c'est fermé
 */
function trouverPlageEnCours(array $horaires, DateTimeImmutable $moment): ?array
{
    foreach (lirePlagesDuJour($horaires, $moment) as $plage) {
        if ($moment >= $plage['debut'] && $moment < $plage['fin']) {
            return $plage;
        }
    }
    return null;
}


/**
 * Cherche la fermeture exceptionnelle en cours à un moment donné.
 *
 * @param array             $fermetures  liste de ['debut', 'fin']
 * @param DateTimeImmutable $moment      moment voulu
 * @return array|null  la fermeture en cours, ou null s'il n'y en a pas
 */
function trouverFermetureEnCours(array $fermetures, DateTimeImmutable $moment): ?array
{
    foreach ($fermetures as $fermeture) {
        if ($moment >= $fermeture['debut'] && $moment < $fermeture['fin']) {
            return $fermeture;
        }
    }
    return null;
}


/**
 * Cherche le prochain moment où la carrosserie sera ouverte (dans les
 * 3 semaines à venir) : un début de plage, ou la fin d'une fermeture
 * exceptionnelle si elle tombe pendant une plage.
 *
 * @param array             $horaires    horaires de config.php
 * @param array             $fermetures  fermetures exceptionnelles
 * @param DateTimeImmutable $maintenant  moment de la visite
 * @return DateTimeImmutable|null  moment de la réouverture, ou null si aucun trouvé
 */
function trouverProchaineOuverture(array $horaires, array $fermetures, DateTimeImmutable $maintenant): ?DateTimeImmutable
{
    // Moments « candidats » : débuts de plages et fins de fermetures.
    $candidats = [];
    $jour = $maintenant->setTime(0, 0);
    for ($i = 0; $i < 21; $i++) {
        foreach (lirePlagesDuJour($horaires, $jour->modify("+$i day")) as $plage) {
            $candidats[] = $plage['debut'];
        }
    }
    foreach ($fermetures as $fermeture) {
        $candidats[] = $fermeture['fin'];
    }
    sort($candidats);

    // Le premier candidat à venir où c'est réellement ouvert.
    foreach ($candidats as $moment) {
        if ($moment > $maintenant
            && trouverPlageEnCours($horaires, $moment) !== null
            && trouverFermetureEnCours($fermetures, $moment) === null) {
            return $moment;
        }
    }
    return null;
}


/**
 * Indique si un jour est férié en France (dates fixes, et fêtes qui
 * dépendent de Pâques : lundi de Pâques, Ascension, lundi de Pentecôte).
 *
 * @param DateTimeImmutable $jour  le jour voulu
 * @return bool  true si le jour est férié
 */
function estJourFerie(DateTimeImmutable $jour): bool
{
    $annee = (int) $jour->format('Y');
    $paques = calculerDatePaques($annee);

    $feries = [
        "$annee-01-01",                               // Jour de l'an
        $paques->modify('+1 day')->format('Y-m-d'),   // Lundi de Pâques
        "$annee-05-01",                               // Fête du travail
        "$annee-05-08",                               // Victoire 1945
        $paques->modify('+39 day')->format('Y-m-d'),  // Ascension
        $paques->modify('+50 day')->format('Y-m-d'),  // Lundi de Pentecôte
        "$annee-07-14",                               // Fête nationale
        "$annee-08-15",                               // Assomption
        "$annee-11-01",                               // Toussaint
        "$annee-11-11",                               // Armistice 1918
        "$annee-12-25",                               // Noël
    ];
    return in_array($jour->format('Y-m-d'), $feries, true);
}


/**
 * Calcule la date du dimanche de Pâques d'une année (méthode de calcul
 * classique, dite « de Meeus » ; ne dépend d'aucune extension de PHP).
 *
 * @param int $annee  l'année voulue (ex. 2027)
 * @return DateTimeImmutable  le dimanche de Pâques
 */
function calculerDatePaques(int $annee): DateTimeImmutable
{
    $a = $annee % 19;
    $b = intdiv($annee, 100);
    $c = $annee % 100;
    $d = intdiv($b, 4);
    $e = $b % 4;
    $f = intdiv($b + 8, 25);
    $g = intdiv($b - $f + 1, 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = intdiv($c, 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = intdiv($a + 11 * $h + 22 * $l, 451);
    $mois = intdiv($h + $l - 7 * $m + 114, 31);
    $jour = (($h + $l - 7 * $m + 114) % 31) + 1;

    return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $annee, $mois, $jour));
}


/**
 * Écrit une heure à la française : « 8h30 », « 14h00 ».
 *
 * @param DateTimeImmutable $moment  le moment voulu
 * @return string  l'heure écrite
 */
function ecrireHeure(DateTimeImmutable $moment): string
{
    return $moment->format('G') . 'h' . $moment->format('i');
}


/**
 * Écrit un jour par rapport à aujourd'hui : « aujourd'hui », « demain »,
 * « lundi » (dans la semaine), ou « lundi 12 octobre » (plus tard).
 *
 * @param DateTimeImmutable $moment      le jour voulu
 * @param DateTimeImmutable $maintenant  aujourd'hui
 * @return string  le jour écrit
 */
function ecrireJourRelatif(DateTimeImmutable $moment, DateTimeImmutable $maintenant): string
{
    $ecart = (int) $maintenant->setTime(0, 0)->diff($moment->setTime(0, 0))->format('%a');
    $nomDuJour = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][(int) $moment->format('w')];
    $nomDuMois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
                  'août', 'septembre', 'octobre', 'novembre', 'décembre'][(int) $moment->format('n') - 1];

    if ($ecart === 0) {
        return 'aujourd\'hui';
    }
    if ($ecart === 1) {
        return 'demain';
    }
    if ($ecart < 7) {
        return $nomDuJour;
    }
    return $nomDuJour . ' ' . $moment->format('j') . ' ' . $nomDuMois;
}
