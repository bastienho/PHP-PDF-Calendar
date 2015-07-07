<?php

function includ($url) {
    if (ini_get('allow_url_include') || is_file($url)) {
        return true;
    } else {
        $fp = fopen($url, 'rb');
        $val = '';
        while (!feof($fp)) {
            $val.=fgets($fp, 4096);
        }
        $val = trim($val);
        return substr($val, 5, strlen($val) - 2);
    }
}

require("fpdf.php");
define('FPDF_FONTPATH', 'http://www.adeli.wac.fr/libs/fpdf/font/');
include ('../wp-config.php');
//define('FPDF_FONTPATH','.');


$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME);


$lan = isset($_GET['annee']) ? $_GET['annee'] :$lan = date('Y');

$debut = strtotime("-".(isset($_GET['days_before'])?$_GET['days_before']:7)." days", mktime(0, 0, 0, 12, 31, $lan - 1));
$debut = strtotime("-" . (6 - date("w", $debut)) . " days", $debut);

$final = strtotime("+".(isset($_GET['days_after'])?$_GET['days_after']:7)." days", mktime(0, 0, 0, 1, 1, $lan + 1));
$final = strtotime("+" . (8 - date("w", $final)) . " days", $final);
$jour = $debut;



$day = 60 * 60 * 24;
//Document size
$d_l = 240;
$d_h = 325;

$entete_top = 6;

//Semainier
$ligne_start = 35;
$ligne_end = 75;
$heure_start = 8;
$heure_fin = 19.5;

$largeur_colone = ($d_l - 10) / 3;
$hauteur_colone = $d_h - $ligne_end;
$hauteur_ligne = $hauteur_colone / ($heure_fin - $heure_start); //22;
//Mois et jours
$mois_c = array("", "JANVIER", "FEVRIER", "MARS", "AVRIL", "MAI", "JUIN", "JUILLET", "AOUT", "SEPTEMBRE", "OCTOBRE", "NOVEMBRE", "DECEMBRE");
$mois_p = array("", "JANV", "FEV", "MARS", "AVRIL", "MAI", "JUIN", "JUIL", "AOUT", "SEPT", "OCT", "NOV", "DEC");
$semaine = array("DIMANCHE", "LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI", "SAMEDI", "DIMANCHE");
$semaine_p = array("DIM", "LUN", "MAR", "MER", "JEU", "VEN", "SAM", "DIM");

function human_date($date) {
    global $mois_c, $semaine;
    return strtolower($semaine[date('w', $date)]) . ' ' . date('d', $date) . ' ' . strtolower($mois_c[date('n', $date)]);
}

function couleur($col) {
    global $pdf;
    //$dk = array('Draw','Text');
    if ($col === 'bleu') {
        $pdf->SetTextSpotColor('PANTONE 293 C');
        $pdf->SetDrawSpotColor('PANTONE 293 C');
        //$pdf->SetFillSpotColor('PANTONE 293 C');
        //$r=0; $v=36; $b=116;
    } elseif ($col === 'gris') {

        $pdf->SetTextSpotColor('PANTONE Process Black C', 40);
        $pdf->SetDrawSpotColor('PANTONE Process Black C', 40);
        // $pdf->SetFillSpotColor('PANTONE Process Black C', 40);
        //$r=$v=$b=125;
    } elseif ($col === 'blanc') {
        //$r=$v=$b=255; 

        $pdf->SetTextSpotColor('WHITE');
        $pdf->SetDrawSpotColor('WHITE');
        // $pdf->SetFillSpotColor('WHITE');
    } else {
        // $col = $r=$v=$b=0; 
        $pdf->SetTextSpotColor('PANTONE Process Black C');
        $pdf->SetDrawSpotColor('PANTONE Process Black C');
        //  $pdf->SetFillSpotColor('PANTONE Process Black C');
    }
    //$pdf->SetTextColor($r,$v,$b);
    //$pdf->SetDrawColor($r,$v,$b);	
}

function fond($col) {
    global $pdf;
    //$dk = array('Draw','Text');
    if ($col === 'bleu') {
        $pdf->SetFillSpotColor('PANTONE 293 C', 60);
        //$r=0; $v=36; $b=116;
    } elseif ($col === 'gris') {
        $pdf->SetFillSpotColor('PANTONE Process Black C', 30);
        //$r=$v=$b=125;
    } elseif ($col === 'blanc') {
        $pdf->SetFillSpotColor('WHITE', 60);
    } else {
        $pdf->SetFillSpotColor('PANTONE Process Black C', 60);
    }
}

function planning($annee) {
    global $pdf, $d_h, $d_l, $mois_c, $semaine;
    $largeur = 200;
    $hauteur = 200;
    $hs = 30;
    $gauche = ($d_l - $largeur) / 2;
    $haut = ($d_h - $hauteur) / 2;
    for ($m = 1; $m <= 12; $m++) {
        if ($m == 1 || $m == 7) {
            $c = 0;
            plat();
            $pdf->AddPage();
            couleur('bleu');
            $pdf->SetFont('helvetica', 'B', 16);
            //$pdf->Rect( $gauche, $hs+($hauteur/31),$largeur,$hauteur);
            //$pdf->Line($gauche+17,$haut,$gauche+17,$haut+$hauteur);
            $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs - 15);
            $pdf->Cell($largeur, 5, "planning $annee", 0, 0, "C", 0);
        }
        couleur('noir');
        $pdf->SetFont('helvetica', 'B', 14);
        //$pdf->Line($gauche,$haut+($c*$hauteur/6),$gauche+$largeur,$haut+($c*$hauteur/6));
        $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs - 5);
        $pdf->Cell($largeur / 6, $hauteur / 31, $mois_c[$m], 0, 0, "C", 0);

        $daystart = mktime(0, 0, 0, $m, 1, $annee);
        $nbj = date("t", $daystart);

        for ($d = 1; $d <= $nbj; $d++) {
            $day = mktime(0, 0, 0, $m, $d, $annee);
            $w = date('w', $day);
            couleur('noir');
            $p = '';
            $f = 0;
            if ($w == 0)
                $f = 1;
            if ($d < 10)
                $p = '  ';

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs + ($d * $hauteur / 31));
            $pdf->Cell($largeur / 6, $hauteur / 31, substr($semaine[$w], 0, 1) . "$p $d", 1, 0, "L", $f);
            //$pdf->RotatedText($gauche+20+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-5,substr($semaine[$w],0,1)."$p $d",90);
            //if($w==0) $pdf->Line($gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-2,$gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-($hauteur/6)+2);
            if ($w == 1) {
                couleur('gris');
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6) + 10, $hs + ($d * $hauteur / 31));
                $pdf->Cell(($largeur / 6) - 10, $hauteur / 31, "semaine " . date('W', $day), 0, 0, "R", 0);
            }
        }
        $c++;
    }
}

function scol($annee) {
    global $pdf, $d_h, $d_l, $mois_c, $semaine, $lan;
    $largeur = 180;
    $hauteur = 110;
    $hs = 40;
    $gauche = ($d_l - $largeur) / 2;
    $haut = ($d_h - $hauteur) / 2;



    for ($m = 1; $m <= 12; $m++) {
        if ($m == 1 || $m == 7) {
            if ($m == 1) {
                plat();
                $pdf->AddPage();
            }
            if ($m == 7) {
                $hs += $hauteur + ($d_h - ($hs * 2) - ($hauteur * 2));
            }
            $c = 0;
        }
        couleur('noir');
        $pdf->Rect($gauche + (($c) * ($largeur / 6)), $hs - 5, $largeur / 6, $hauteur + 10);

        couleur('bleu');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs - 5);
        $pdf->Cell($largeur / 6, 7, $mois_c[$m], 1, 1, "C", 0);

        $daystart = mktime(0, 0, 0, $m, 1, $annee);
        $nbj = date("t", $daystart);

        for ($d = 1; $d <= $nbj; $d++) {
            $day = mktime(0, 0, 0, $m, $d, $annee);
            $w = date('w', $day);
            couleur('noir');
            $p = '';
            if ($d < 10)
                $p = '  ';

            if ($w == 0) {
                couleur('bleu');
                $pdf->Line((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs + ($d * $hauteur / 31) + ($hauteur / 31), (($d_l - $largeur) / 2) + ($c * $largeur / 6) + ($largeur / 6), $hs + ($d * $hauteur / 31) + ($hauteur / 31));
            }

            $res = mysql_query("SELECT `fetedujour`,`ferie`,`fete` FROM `fetes` WHERE `lejour`='$d' AND `lemois`='$m'");
            $ro = mysql_fetch_array($res);
            $saint = $ro[0];
            if ($ro[2] != '')
                $saint = $ro[2];

            $res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='" . date('Y-m-d', $day) . "' OR  `jour`='0000-" . date('m-d', $day) . "'");
            if ($res && mysql_num_rows($res) > 0) {
                $ro = mysql_fetch_array($res);
                $saint = $ro[0];
            }

            $saint = utf8_decode(str_replace("Saint", "St", str_replace("Sainte", "Ste", $saint)));

            $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6) + 0.2, $hs + ($d * $hauteur / 31) + 0.2);
            if ($ro[1] == 1 && $annee == $lan)
                $pdf->Cell(($largeur / 6 - 8) + 7.5, ($hauteur / 31) - 0.4, '', 0, 0, "R", 1);


            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6), $hs + ($d * $hauteur / 31));
            $pdf->Cell($largeur / 6, $hauteur / 31, substr($semaine[$w], 0, 1) . "$p", 0, 0, "L", 0);

            $pdf->SetX((($d_l - $largeur) / 2) + ($c * $largeur / 6) + 3);
            $pdf->Cell($largeur / 6, $hauteur / 31, "$p $d", 0, 0, "L", 0);

            $pdf->SetFont('helvetica', '', 4.5);
            $pdf->SetXY((($d_l - $largeur) / 2) + ($c * $largeur / 6) + 8, $hs + ($d * $hauteur / 31));
            $pdf->Cell(($largeur / 6 - 8), $hauteur / 31, $saint, 0, 0, "L", $f);
        }
        $c++;
    }

    couleur('bleu');
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetXY($gauche, 20);
    $pdf->Cell($largeur, 16, "calendrier $annee", 0, 0, "C", 0);
}

function calendrier($annee) {
    global $pdf, $d_h, $d_l, $mois_c, $semaine;
    $largeur = 200;
    $hauteur = 290;
    $gauche = ($d_l - $largeur) / 2;
    $haut = ($d_h - $hauteur) / 2;
    for ($m = 1; $m <= 12; $m++) {
        if ($m == 1 || $m == 7) {
            $c = 5;
            plat();
            $pdf->AddPage();
            couleur('bleu');
            $pdf->Rect($gauche, $haut, $largeur, $hauteur);
            $pdf->Line($gauche + 17, $haut, $gauche + 17, $haut + $hauteur);
        }
        couleur('bleu');
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Line($gauche, $haut + ($c * $hauteur / 6), $gauche + $largeur, $haut + ($c * $hauteur / 6));

        $pdf->RotatedText($gauche + 10, $haut + (($c + 1) * $hauteur / 6) - 5, $mois_c[$m], 90);

        $daystart = mktime(0, 0, 0, $m, 1, $annee);
        $nbj = date("t", $daystart);

        for ($d = 1; $d <= $nbj; $d++) {
            $day = mktime(0, 0, 0, $m, $d, $annee);
            $w = date('w', $day);
            couleur('gris');
            $p = '';
            if ($w == 0)
                couleur('bleu');
            if ($d < 10)
                $p = '  ';
            $res = mysql_query("SELECT `fetedujour`,`ferie` FROM `fetes` WHERE `lejour`='$d' AND `lemois`='$m'");
            $ro = mysql_fetch_array($res);
            $saint = utf8_decode(str_replace("Saint", "St", str_replace("Sainte", "Ste", $ro[0])));
            
            $res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='" . date('Y-m-d', $day) . "' OR  `jour`='0000-" . date('m-d', $day) . "'");
            if ($res && mysql_num_rows($res) > 0) {
                $ro = mysql_fetch_array($res);
                $saint = utf8_decode($ro[0]);
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->RotatedText($gauche + 20 + ($d * (($largeur - 25) / 31)), $haut + (($c + 1) * $hauteur / 6) - 5, substr($semaine[$w], 0, 1) . "$p $d", 90);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->RotatedText($gauche + 20 + ($d * (($largeur - 25) / 31)), $haut + (($c + 1) * $hauteur / 6) - 13, $saint, 90);
            if ($w == 0)
                $pdf->Line($gauche + 22 + ($d * (($largeur - 25) / 31)), $haut + (($c + 1) * $hauteur / 6) - 2, $gauche + 22 + ($d * (($largeur - 25) / 31)), $haut + (($c + 1) * $hauteur / 6) - ($hauteur / 6) + 2);
        }
        $c--;
    }
}

function plat() {
    global $pdf, $d_l, $d_h;
    $pdf->SetAlpha(0.01);
    $pdf->SetFillSpotColor('WHITE');
    $pdf->Rect(0, 0, $d_l, $d_h, 'F');
    $pdf->SetAlpha(1);
}

$pdf = new PDF('P', 'mm', array($d_l, $d_h));
$pdf->AddSpotColor('PANTONE 293 C', 93, 72, 0, 0);
$pdf->AddSpotColor('PANTONE Process Black C', 0, 0, 0, 100);
$pdf->AddSpotColor('WHITE', 0, 0, 0, 0);

$pdf->Open();
$pdf->SetTitle("agenda SNMR $lan");
$pdf->SetAutoPageBreak(0);
$pdf->SetFillColor(175, 175, 175);
//$pdf->AddFont('helvetica');

scol($lan);
planning($lan);
calendrier($lan);

$pdf->AddPage();

couleur('bleu');
$pdf->SetFont('helvetica', '', 12);

$i = 0;
$c = 0;
$d = 0;

//$final = $jour;


function pieddepage() {
    global $pdf, $d_l;
    $pdf->SetXY(5, $d_h - 10);
    $pdf->SetFont('helvetica', 'B', 8);
    couleur('gris');
    $pdf->Cell($d_l - 10, 2, utf8_decode("Syndicat National des Médecins Rhumatologues"), 0, 0, 'C');
    $pdf->SetXY(5, $d_h - 7);
    couleur('bleu');
    $pdf->Cell($d_l - 10, 2, "www.snmr.org", 0, 0, 'C');
}


/*
 * SEMAINIER
 */
while ($jour < $final) {
    $s = date("w", $jour);
    $j = date("j", $jour);
    $m = date("n", $jour);

    // Page de gauche
    if ($s == 1) {
        plat();
        $pdf->AddPage();
        $c = 0;
        $d = 5;
        $pdf->SetFont('helvetica', 'B', 16);

        $x = 6;
        if (date("n", $jour) == date("n", strtotime("+6 days", $jour))) {
            $mois_annee = $mois_c[date("n", $jour)];
        } else {
            $mois_annee = $mois_p[date("n", $jour)] . '-' . $mois_p[date("n", strtotime("+6 days", $jour))];
        }
        $pdf->SetXY($x, $entete_top);
        couleur('bleu');
        $pdf->Cell(40, 8, $mois_annee, 0, 0, "L", 0);

        $x+=$pdf->GetStringWidth($mois_annee) + 1;
        $pdf->SetXY($x, $entete_top);
        couleur('gris');
        $pdf->Cell(40, 8, date("Y", $jour), 0, 0, "L", 0);

        $pdf->SetXY(9, $d_h - 33);
        $pdf->SetFont('helvetica', 'B', 12);
        couleur('bleu');
        $pdf->Cell(40, 8, 'NOTES', 0, 0, "L", 0);


        couleur('gris');
        for ($l = $d_h - 25; $l <= $d_h - 15; $l+=5) {
            $pdf->Line(9, $l, $d_l - 11, $l);
        }
        couleur('blanc');
        for ($l = 9; $l < $d_l - 11; $l+=0.5) {
            $pdf->Line($l, $d_h - 25, $l, $d_h - 15);
        }

        pieddepage();
    }
    // Page de droite
    elseif ($s == 4) {
        plat();
        $pdf->AddPage();
        $c = 0;
        $d = 5;

        if (date("W", $jour) < 53) {


            // NUMERO DE SEMAINE
            if (date("W", $jour) == 1)
                $eme = utf8_decode('ère');
            else
                $eme = 'e';

            couleur('bleu');
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetXY($d_l - 50, $entete_top);
            $pdf->Cell(10, 8, abs(date("W", $jour)), 0, 0, "R", 0);

            $pdf->SetXY($d_l - 42, $entete_top - 1);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(5, 8, $eme, 0, 0, "L", 0);

            $pdf->SetXY($d_l - 35, $entete_top);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(15, 8, "Semaine", 0, 0, "L", 0);
        }
        //pieddepage();
    }


// Dimanche
    if ($s == 0) {

        $pdf->SetXY(9, $d_h - 33);
        $pdf->SetFont('helvetica', 'B', 12);
        couleur('noir');
        $pdf->Cell($largeur_colone - 15, 8, $semaine[$s], 0, 0, "L", 0);
        $pdf->SetXY(9, $d_h - 33);
        $pdf->SetFont('helvetica', 'B', 22);
        couleur('bleu');
        $pdf->Cell($largeur_colone - 10, 8, date("d", $jour), 0, 0, "R", 0);

        // Saint
        $res = mysql_query("SELECT `fetedujour`,`ferie`,`fete` FROM `fetes` WHERE `lejour`='$j' AND `lemois`='$m'");
        $ro = array('', '', '');
        if ($res && mysql_num_rows($res) > 0) {
            $ro = mysql_fetch_array($res);
        }
        $saint = $ro['fetedujour'];
        if ($ro['fete'] != '')
            $saint = $ro['fete'];
        
        $res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='" . date('Y-m-d', $jour) . "' OR  `jour`='0000-" . date('m-d', $jour) . "'");
            if ($res && mysql_num_rows($res) > 0) {
                $fer = mysql_fetch_array($res);
                $saint = $fer['nom'];
                if($fer['bool']==1) $ro['ferie'] = 1;
            }
            
            
        $saint = utf8_decode($saint);
        $pdf->SetXY(9, $d_h - 29);
        $pdf->SetFont('helvetica', '', 6);
        couleur('bleu');
        $pdf->Cell($largeur_colone - 20, 2, $saint, 0, 0, "R", 0);
        couleur('gris');
        for ($l = $d_h - 25; $l <= $d_h - 10; $l+=5) {
            $pdf->Line(9, $l, $d_l - 11, $l);
        }
        couleur('blanc');
        for ($l = 9; $l < $d_l - 11; $l+=0.5) {
            $pdf->Line($l, $d_h - 20, $l, $d_h - 10);
        }

        //CONGRES
        $xt = $largeur_colone - 2;
        $yt = $d_h - 31;
        /*
          //echo 'semaine '.date("W",$jour).'<br><br>';
          $pdf->SetXY(181,180);
          $pdf->SetFont('helvetica','B',12);
          couleur('noir');
          $pdf->Cell($largeur_colone-15,8,$semaine[$s], 0,0, "L", 0);

          $pdf->SetXY(180,180);
          $pdf->SetFont('helvetica','B',22);
          couleur('bleu');
          $pdf->Cell($largeur_colone-10,8,date("d",$jour), 0,0, "R", 0);

          $res = mysql_query("SELECT `fetedujour`,`ferie`,`fete` FROM `fetes` WHERE `lejour`='$j' AND `lemois`='$m'");
          $ro = mysql_fetch_array($res);
          $saint = $ro[0];
          if($ro[2]!='') $saint = $ro[2];

          $res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='".date('Y-m-d',$jour)."'");
          if($res && mysql_num_rows($res)>0){
          $ro = mysql_fetch_array($res);
          $saint = $ro[0];
          }
          $saint=utf8_decode($saint);
          $pdf->SetXY(180,184);
          $pdf->SetFont('helvetica','',6);
          //$pdf->SetTextColor(100, 100, 100);
          couleur('bleu');
          $pdf->Cell($largeur_colone-10,8,$saint, 0,0, "R", 0);

          couleur('gris');
          for($l=195 ; $l<255 ; $l+=5){
          $pdf->Line(180,$l,225,$l);
          }
          couleur('blanc');
          for($l=179 ; $l<226 ; $l+=0.5){
          $pdf->Line($l,194,$l,271);
          }
          couleur('noir');

          $pdf->Line(181,190,225,190);
          $pdf->Line(181,180,181,190);

          if($ro[1]==1){
          $pdf->SetFont('helvetica','',22);
          couleur('bleu');
          $pdf->RotatedText(200,230,utf8_decode("FÉRIÉ"),45);
          } */
    }
// JOURS
    else {
        // Jour de la smeaine
        $pdf->SetXY($d + ($c * $largeur_colone) + 3, $entete_top + 8);
        $pdf->SetFont('helvetica', 'B', 14);
        couleur('noir');
        $pdf->Cell($largeur_colone - 5, 8, $semaine[$s], 0, 0, "L", 0);

        // Numéro du mois
        $pdf->SetXY($d + ($c * $largeur_colone), $entete_top + 7);
        $pdf->SetFont('helvetica', 'B', 24);
        couleur('bleu');
        $pdf->Cell($largeur_colone - 3, 8, date("d", $jour), 0, 0, "R", 0);

        // Saint
        $res = mysql_query("SELECT `fetedujour`,`ferie`,`fete` FROM `fetes` WHERE `lejour`='$j' AND `lemois`='$m'");
        $ro = array('', '', '');
        if ($res && mysql_num_rows($res) > 0) {
            $ro = mysql_fetch_array($res);
        }
        $saint = $ro['fetedujour'];
        if ($ro['fete'] != '')
            $saint = $ro['fete'];
        
        $res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='" . date('Y-m-d', $jour) . "' OR  `jour`='0000-" . date('m-d', $jour) . "'");
            if ($res && mysql_num_rows($res) > 0) {
                $fer = mysql_fetch_array($res);
                $saint = $fer['nom'];
                if($fer['bool']==1) $ro['ferie'] = 1;
            }
            
            
        $saint = utf8_decode($saint);
        $pdf->SetXY($d + ($c * $largeur_colone), $entete_top + 12);
        $pdf->SetFont('helvetica', '', 6);
        couleur('bleu');
        $pdf->Cell($largeur_colone - 13, 2, $saint, 0, 0, "R", 0);

        // Traits
        couleur('gris');
        $pdf->Line($d + ($c * $largeur_colone) + 3, $entete_top + 15, $d + ($c * $largeur_colone) + $largeur_colone - 5, $entete_top + 15);
        //$pdf->Line($d+($c*$largeur_colone)-1,270,$d+($c*$largeur_colone)+55,270);
        //couleur('noir');
        //$pdf->Line($d + 55 + ($c * $largeur_colone), 11, $d + 55 + ($c * $largeur_colone), 24);

        for ($h = $heure_start; $h <= $heure_fin; $h++) {

            $th = $h - $heure_start;
            $hot = $ligne_start + ($th * $hauteur_ligne);

            $xs = $d + ($c * $largeur_colone) + 4;
            $xe = $xs + $largeur_colone - 10;
            $xt = $xs - 1;


            $pdf->SetLineWidth(0.2);

            if ($h < $heure_fin) {
                $hot+=$hauteur_ligne / 4;

                $pdf->SetFont('helvetica', '', 6);
                couleur('gris');
                $pdf->Line($xs + 6, $hot + 4, $xe, $hot + 4);

                $hot+=$hauteur_ligne / 4;
                $pdf->Line($xs + 6, $hot + 4, $xe, $hot + 4);
                if ($h < floor($heure_fin)) {
                    $hot+=$hauteur_ligne / 4;
                    $pdf->Line($xs + 6, $hot + 4, $xe, $hot + 4);
                }
            }
        }
        couleur('blanc');
        for ($l = $xs - 1; $l < $xe + 1; $l+=0.5) {
            $pdf->Line($l, 35, $l, $hauteur_colone + $ligne_start+5);
        }

        // Heures, minutes
        for ($h = $heure_start; $h <= $heure_fin; $h++) {

            $th = $h - $heure_start;
            $hot = $ligne_start + ($th * $hauteur_ligne);

            $xs = $d + ($c * $largeur_colone) + 4;
            $xe = $xs + $largeur_colone - 10;
            $xt = $xs - 5;
            $yt = $entete_top + 15.5;
            couleur('gris');
            $pdf->SetXY($xt + 4, $hot - 2.5);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(10, 8, $h . 'h', 0, 0, "R", 0);
            couleur('bleu');
            $pdf->SetLineWidth(0.1);
            $pdf->Line($xs, $hot + 4, $xe, $hot + 4);

            if ($h < $heure_fin) {
                $hot+=$hauteur_ligne / 4;

                $pdf->SetFont('helvetica', '', 6);
                couleur('gris');

                $pdf->SetXY($xt, $hot - 2);
                $pdf->Cell(10, 8, "15", 0, 0, "R", 0);


                $hot+=$hauteur_ligne / 4;

                $pdf->SetXY($xt, $hot - 2);
                $pdf->Cell(10, 8, "30", 0, 0, "R", 0);

                if ($h < floor($heure_fin)) {

                    $hot+=$hauteur_ligne / 4;

                    $pdf->SetXY($xt, $hot - 2);
                    $pdf->Cell(10, 8, "45", 0, 0, "R", 0);
                }
            }
        }


        //FERIE
        if ($ro['ferie'] == 1) {
            $pdf->SetFont('helvetica', 'B', 24);
            couleur('bleu');
            $pdf->RotatedText($d + ($largeur_colone / 2) + ($c * $largeur_colone), 150, utf8_decode("FÉRIÉ"), 45);
        }
    }
    // CONGRES
    $sql_jour = date('Y-m-d', $jour);
    $req = ("SELECT `nom`,`lieu`,`comment`,`date_debut`,`date_fin` FROM `congres` WHERE `date_debut`<='" . $sql_jour . "' AND `date_fin`>='" . $sql_jour . "' AND active='1'");
    $res = mysql_query($req);


    if ($res && mysql_num_rows($res) > 0) {
        $congres_x = $xt + 4;
        $congres_y = $yt;
        couleur('blanc');
        fond('bleu');

        $pdf->SetFont('helvetica', '', 8.5);
        while ($congres = mysql_fetch_array($res)) {
            $congres_txt = '';
            $congres_txt.=$congres['nom'] .
                    (!empty($congres['lieu']) ? ', ' . ucfirst(strtolower($congres['lieu'])) : '') .
                    //(!empty($congres['comment']) ? ', ' . $congres['comment'] : '').
                    '';

            $pdf->SetXY($congres_x, $congres_y);

            $pdf->MultiCell($largeur_colone - 8, 4, utf8_decode($congres_txt), 0, 'L', 1);
            if ($s == 0) {
                $congres_x += $largeur_colone;
            } else {
                $congres_y = $pdf->getY() + 0.5;
            }
        }
        fond('blanc');
    }



    $i++;
    $c++;
    $jour+=$day;
}

planning($lan + 1);
scol($lan + 1);
calendrier($lan + 1);

$pdf->Output();
