<?php
function includ($url){	
	if(ini_get('allow_url_include') || is_file($url)){
		return true;	
	}
	else{
		$fp=fopen($url,'rb');
		$val='';
		while(!feof($fp)){
			$val.=fgets($fp, 4096);	
		}
		$val = trim($val);
		return substr($val,5,strlen($val)-2);
	}
}
require("fpdf.php");
define('FPDF_FONTPATH','http://www.adeli.wac.fr/libs/fpdf/font/');
include ('../wp-config.php');
//define('FPDF_FONTPATH','./');


$conn=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME);


if(isset($_GET['annee'])){
	$lan = $_GET['annee'];
}
else{
	$lan = date('Y');
}
$debut = strtotime("-7 days", mktime(0,0,0,12,31,$lan-1));
$debut = strtotime("-".(6-date("w",$debut))." days", $debut);

$final = strtotime("+7 days", mktime(0,0,0,1,1,$lan+1));
$final = strtotime("+".(8-date("w",$final))." days", $final);
$jour = $debut;



$day = 60*60*24;

$largeur_colone=55;
$hauteur_ligne=18;
$ligneh = 80;
$ligne_start = 30;
$heure_start = 8;
$heure_fin = 20;

$d_l=240;
$d_h=325;

$mois_c = array("","JANVIER","FEVRIER","MARS","AVRIL","MAI","JUIN","JUILLET","AOUT","SEPTEMBRE","OCTOBRE","NOVEMBRE","DECEMBRE");
$mois_p = array("","JANV","FEV","MARS","AVRIL","MAI","JUIN","JUIL","AOUT","SEPT","OCT","NOV","DEC");
$semaine = array("DIMANCHE","LUNDI","MARDI","MERCREDI","JEUDI","VENDREDI","SAMEDI","DIMANCHE");


function couleur($col){
	global $pdf;
	//$dk = array('Draw','Text');
	if($col==='bleu'){ 
		 $pdf->SetTextSpotColor('PANTONE 293 C');
		 $pdf->SetDrawSpotColor('PANTONE 293 C');
	 //$r=0; $v=36; $b=116;
	 }
	elseif($col==='gris'){ 
		
		 $pdf->SetTextSpotColor('PANTONE Process Black C',40);
		 $pdf->SetDrawSpotColor('PANTONE Process Black C',40);
		//$r=$v=$b=125;
	}
	elseif($col==='blanc'){ 
	//$r=$v=$b=255; 
	
		 $pdf->SetTextSpotColor('WHITE');
		 $pdf->SetDrawSpotColor('WHITE');
	}	
	else{
		// $col = $r=$v=$b=0; 
		 $pdf->SetTextSpotColor('PANTONE Process Black C');
		 $pdf->SetDrawSpotColor('PANTONE Process Black C');
	 }
	//$pdf->SetTextColor($r,$v,$b);
	//$pdf->SetDrawColor($r,$v,$b);	
}
function planning($annee){
	global $pdf,$d_h,$d_l,$mois_c,$semaine;
	$largeur = 200;
	$hauteur = 200;
	$hs = 30;
	$gauche = ($d_l-$largeur)/2;
	$haut = ($d_h-$hauteur)/2;
	for($m=1 ; $m<=12 ; $m++){
		if($m==1 || $m==7){
			$c=0;
			plat();
$pdf->AddPage();
			couleur('bleu');
			$pdf->SetFont('Times','B',16);
			//$pdf->Rect( $gauche, $hs+($hauteur/31),$largeur,$hauteur);
			//$pdf->Line($gauche+17,$haut,$gauche+17,$haut+$hauteur);
			$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6),$hs-15);
			$pdf->Cell($largeur,5,"planning $annee", 0,0, "C", 0);
		}
		couleur('noir');
		$pdf->SetFont('Times','B',14);
		//$pdf->Line($gauche,$haut+($c*$hauteur/6),$gauche+$largeur,$haut+($c*$hauteur/6));
		$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6),$hs-5);
		$pdf->Cell($largeur/6,$hauteur/31,$mois_c[$m], 0,0, "C", 0);
		
		$daystart = mktime(0,0,0,$m,1,$annee);
		$nbj = date("t",$daystart);
		
		for($d=1 ; $d<=$nbj ; $d++){
			$day = mktime(0,0,0,$m,$d,$annee);
			$w = date('w',$day);
			couleur('noir');
			$p='';
			$f=0;
			if($w==0) $f=1;
			if($d<10) $p='  ';
			
			$pdf->SetFont('Times','B',9);
			$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6),$hs+($d*$hauteur/31));
			$pdf->Cell($largeur/6,$hauteur/31,substr($semaine[$w],0,1)."$p $d", 1,0, "L", $f);
			//$pdf->RotatedText($gauche+20+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-5,substr($semaine[$w],0,1)."$p $d",90);
			//if($w==0) $pdf->Line($gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-2,$gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-($hauteur/6)+2);
			if($w==1){
				couleur('gris');
				$pdf->SetFont('Times','',9);
				$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6)+10,$hs+($d*$hauteur/31));
				$pdf->Cell(($largeur/6)-10,$hauteur/31,"semaine ".date('W',$day), 0,0, "R", 0);
			}
		}		
		$c++;
	}
	
}
function scol($annee){
	global $pdf,$d_h,$d_l,$mois_c,$semaine,$lan;
	$largeur = 180;
	$hauteur = 110;
	$hs = 40;
	$gauche = ($d_l-$largeur)/2;
	$haut = ($d_h-$hauteur)/2;
	
	

	for($m=1 ; $m<=12 ; $m++){
		if($m==1 || $m==7){
			if($m==1){
				plat();
$pdf->AddPage();
			}
			if($m==7){
				$hs += $hauteur+($d_h-($hs*2)-($hauteur*2));
			}
			$c=0;			
		}
		couleur('noir');
		$pdf->Rect( $gauche+(($c)*($largeur/6)), $hs-5,$largeur/6,$hauteur+10);
			
		couleur('bleu');
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6),$hs-5);
		$pdf->Cell($largeur/6,7,$mois_c[$m], 1,1, "C", 0);
		
		$daystart = mktime(0,0,0,$m,1,$annee);
		$nbj = date("t",$daystart);
		
		for($d=1 ; $d<=$nbj ; $d++){
			$day = mktime(0,0,0,$m,$d,$annee);
			$w = date('w',$day);
			couleur('noir');
			$p='';
			if($d<10) $p='  ';
			
			if($w==0){
				couleur('bleu');
				$pdf->Line((($d_l-$largeur)/2)+($c*$largeur/6),$hs+($d*$hauteur/31)+($hauteur/31),(($d_l-$largeur)/2)+($c*$largeur/6)+($largeur/6),$hs+($d*$hauteur/31)+($hauteur/31));
			}
			
			$res = mysql_query("SELECT `fetedujour`,`ferie`,`fete` FROM `fetes` WHERE `lejour`='$d' AND `lemois`='$m'");
			$ro = mysql_fetch_array($res);
			$saint = $ro[0];
			if($ro[2]!='') $saint = $ro[2];
			
			$res = mysql_query("SELECT `nom`,`bool` FROM `ferie` WHERE `jour`='".date('Y-m-d',$day)."'");
			if($res && mysql_num_rows($res)>0){
				$ro = mysql_fetch_array($res);
				$saint = $ro[0];
			}
			
			$saint = utf8_decode(str_replace("Saint","St",str_replace("Sainte","Ste",$saint)));
			
			$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6)+0.2,$hs+($d*$hauteur/31)+0.2);
			if($ro[1]==1 && $annee==$lan)  $pdf->Cell(($largeur/6-8)+7.5,($hauteur/31)-0.4,'', 0,0, "R", 1);
			
			
			$pdf->SetFont('Times','B',8);
			$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6),$hs+($d*$hauteur/31));
			$pdf->Cell($largeur/6,$hauteur/31,substr($semaine[$w],0,1)."$p", 0,0, "L", 0);
			
			$pdf->SetX((($d_l-$largeur)/2)+($c*$largeur/6)+3);
			$pdf->Cell($largeur/6,$hauteur/31,"$p $d", 0,0, "L", 0);
			
			$pdf->SetFont('Times','',4.5);
			$pdf->SetXY((($d_l-$largeur)/2)+($c*$largeur/6)+8,$hs+($d*$hauteur/31));
			$pdf->Cell(($largeur/6-8),$hauteur/31,$saint, 0,0, "L", $f);
			
		}		
		$c++;
	}
	
	couleur('bleu');
	$pdf->SetFont('Times','B',16);
	$pdf->SetXY($gauche,20);
	$pdf->Cell($largeur,16,"calendrier $annee", 0,0, "C", 0);
}
function calendrier($annee){
	global $pdf,$d_h,$d_l,$mois_c,$semaine;
	$largeur = 200;
	$hauteur = 290;
	$gauche = ($d_l-$largeur)/2;
	$haut = ($d_h-$hauteur)/2;
	for($m=1 ; $m<=12 ; $m++){
		if($m==1 || $m==7){
			$c=5;
			plat();
$pdf->AddPage();
			couleur('bleu');
			$pdf->Rect( $gauche, $haut,$largeur,$hauteur);
			$pdf->Line($gauche+17,$haut,$gauche+17,$haut+$hauteur);
		}
		couleur('bleu');
		$pdf->SetFont('Times','B',18);
		$pdf->Line($gauche,$haut+($c*$hauteur/6),$gauche+$largeur,$haut+($c*$hauteur/6));
		
		$pdf->RotatedText($gauche+10,$haut+(($c+1)*$hauteur/6)-5,$mois_c[$m],90);
		
		$daystart = mktime(0,0,0,$m,1,$annee);
		$nbj = date("t",$daystart);
		
		for($d=1 ; $d<=$nbj ; $d++){
			$day = mktime(0,0,0,$m,$d,$annee);
			$w = date('w',$day);
			couleur('gris');
			$p='';
			if($w==0) couleur('bleu');
			if($d<10) $p='  ';
			$res = mysql_query("SELECT `fetedujour`,`ferie` FROM `fetes` WHERE `lejour`='$d' AND `lemois`='$m'");
			$ro = mysql_fetch_array($res);
			$saint = utf8_decode(str_replace("Saint","St",str_replace("Sainte","Ste",$ro[0])));
			
			$pdf->SetFont('Times','B',9);
			$pdf->RotatedText($gauche+20+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-5,substr($semaine[$w],0,1)."$p $d",90);
			$pdf->SetFont('Times','',8);
			$pdf->RotatedText($gauche+20+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-13,$saint,90);
			if($w==0) $pdf->Line($gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-2,$gauche+22+($d*(($largeur-25)/31)),$haut+(($c+1)*$hauteur/6)-($hauteur/6)+2);
		}		
		$c--;
	}
	
}
function plat(){
	global $pdf,$d_l,$d_h;
	$pdf->SetAlpha(0.01);
	$pdf->SetFillSpotColor('WHITE');
	$pdf->Rect(0,0,$d_l,$d_h,'F');
	$pdf->SetAlpha(1);
}

$pdf = new PDF( 'P', 'mm', array($d_l,$d_h) );
$pdf->AddSpotColor('PANTONE 293 C',93,72,0,0);
$pdf->AddSpotColor('PANTONE Process Black C',0,0,0,100);
$pdf->AddSpotColor('WHITE',0,0,0,0);

$pdf->Open();
$pdf->SetTitle("agenda SNMR $lan");
$pdf->SetAutoPageBreak(0);
$pdf->SetFillColor(175,175,175);
//$pdf->AddFont('Times');

scol($lan);
planning($lan);
calendrier($lan);

$pdf->AddPage();

couleur('bleu');
$pdf->SetFont('Times','',12);

$i=0;
$c=0;
$d=0;
//$final = $jour;
while($jour<$final){
	$s = date("w",$jour);
	$j = date("j",$jour);
	$m = date("n",$jour);
	if($s==1){
		plat();
$pdf->AddPage();
		$c=0;
		$d=60;
		$pdf->Line(59,11,59,24);
		$pdf->SetXY(11,11);
		$pdf->SetFont('Times','B',16);
		//$pdf->SetTextColor(150, 150, 150);
		couleur('bleu');
		if(date("n",$jour) == date("n",strtotime("+6 days",$jour))){
			$pdf->Cell(40,8,$mois_c[date("n",$jour)], 0,0, "L", 0);
		}
		else{
			$pdf->Cell(40,8,$mois_p[date("n",$jour)].'-'.$mois_p[date("n",strtotime("+6 days",$jour))], 0,0, "L", 0);
		}
		$pdf->SetXY(11,16);
		$pdf->SetFont('Times','B',14);
		//$pdf->SetTextColor(40, 40, 40);
		couleur('bleu');
		$pdf->Cell(40,8, date("Y",$jour), 0,0, "L", 0);
		
		$pdf->SetFont('Times','B',14);
		//$pdf->SetTextColor(200, 200, 200);
		couleur('gris');
		$pdf->RotatedText(15,140,"Syndicat National des Médecins Rhumatologues",90);
		couleur('bleu');
		$pdf->RotatedText(21,140,"www.snmr.org",90);
		
		
		$pdf->SetXY(11,183);
		$pdf->SetFont('Times','B',12);
		couleur('noir');
		$pdf->Cell(40,8,'NOTES', 0,0, "L", 0);
		

		couleur('gris');
		for($l=195 ; $l<255 ; $l+=5){
			$pdf->Line(11,$l,50,$l);
		}
		couleur('blanc');
		for($l=10 ; $l<51 ; $l+=0.5){
			$pdf->Line($l,194,$l,271);
		}
		couleur('noir');
		
		$pdf->Line(11,190,50,190);
		$pdf->Line(11,180,11,190);

	}
	if($s==4){
		plat();
$pdf->AddPage();
		$c=0;
		$d=16;
		$pdf->Line(15,11,15,24);
		
		/*$pdf->SetFont('Times','B',16);
		//$pdf->SetTextColor(150, 150, 150);
		couleur('bleu');
		if(date("n",$jour) == date("n",strtotime("+3 days",$jour)) && date("n",$jour) == date("n",strtotime("-3 days",$jour))){
			$pdf->Cell(35,8,$mois_c[date("n",$jour)], 0,0, "R", 0);
		}
		elseif(date("n",$jour) != date("n",strtotime("-3 days",$jour))){
			$pdf->Cell(35,8,$mois_p[date("n",strtotime("-3 days",$jour))].'-'.$mois_p[date("n",$jour)], 0,0, "R", 0);
		}
		else{
			$pdf->Cell(35,8,$mois_p[date("n",$jour)].'-'.$mois_p[date("n",strtotime("+3 days",$jour))], 0,0, "R", 0);
		}	
		$pdf->SetXY(190,16);
		$pdf->SetFont('Times','B',14);
		$pdf->SetTextColor(40, 40, 40);*/
		couleur('bleu');
		
		if(date("W",$jour)<53){
		
			if(date("W",$jour)==1) $eme='ère';
			else $eme='e';
		
		
			$pdf->SetFont('Times','B',16);
			$pdf->SetXY(190,11);
			$pdf->Cell(10,8,abs(date("W",$jour)), 0,0, "R", 0);
		
			$pdf->SetXY(198,10);
			$pdf->SetFont('Times','B',10);
			$pdf->Cell(5,8,$eme, 0,0, "L", 0);
			
			$pdf->SetXY(205,11);
			$pdf->SetFont('Times','B',16);
			$pdf->Cell(15,8,"Semaine", 0,0, "L", 0);
		}
	}
	//echo "".$semaine[$s]." ".date("d/m/Y",$jour)."<br>";
	
	if($s==0){
		//echo 'semaine '.date("W",$jour).'<br><br>';
		$pdf->SetXY(181,180);
		$pdf->SetFont('Times','B',12);
		couleur('noir');
		$pdf->Cell(40,8,$semaine[$s], 0,0, "L", 0);
		
		$pdf->SetXY(180,180);
		$pdf->SetFont('Times','B',22);
		couleur('bleu');
		$pdf->Cell(45,8,date("d",$jour), 0,0, "R", 0);
		
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
		$pdf->SetFont('Times','',6);
		//$pdf->SetTextColor(100, 100, 100);
		couleur('bleu');
		$pdf->Cell(45,8,$saint, 0,0, "R", 0);
		
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
			$pdf->SetFont('Times','',22);
			couleur('bleu');
			$pdf->RotatedText(200,230,"FÉRIÉ",45);
		}
	}
	else{
		$pdf->SetXY($d+($c*$largeur_colone),16);
		$pdf->SetFont('Times','B',12);
		couleur('noir');
		$pdf->Cell(50,8,$semaine[$s], 0,0, "L", 0);
		
		$pdf->SetXY($d+($c*$largeur_colone),11);
		$pdf->SetFont('Times','B',22);
		couleur('bleu');
		$pdf->Cell(54,8,date("d",$jour), 0,0, "R", 0);
		
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
		$pdf->SetXY($d+($c*$largeur_colone),17);
		$pdf->SetFont('Times','',6);
		couleur('bleu');
		$pdf->Cell(54,8,$saint, 0,0, "R", 0);
		
		$pdf->Line($d+($c*$largeur_colone)-1,24,$d+($c*$largeur_colone)+55,24);
		//$pdf->Line($d+($c*$largeur_colone)-1,270,$d+($c*$largeur_colone)+55,270);
		
		for($h=$heure_start ; $h<=$heure_fin ; $h++){
			
			$th = $h-$heure_start;
			$hot = $ligne_start+($th*$hauteur_ligne);
			
			$xs = $d+($c*$largeur_colone)+4;
			$xe = $xs+45;
			$xt = $xs-10;

			$pdf->SetXY($xt, $hot);
			$pdf->SetFont('Times','B',12);
			couleur('bleu');
			$pdf->Cell(10,8,$h, 0,0, "R", 0);
			$pdf->SetLineWidth(0.3);
			$pdf->Line($xs,$hot+4,$xe,$hot+4);
			$pdf->SetLineWidth(0.2);
			
			if($h<$heure_fin){
				$hot+=$hauteur_ligne/4;
				
				$pdf->SetFont('Times','',6);
				couleur('gris');
				
				$pdf->SetXY($xt, $hot);
				$pdf->Cell(10,8,"15", 0,0, "R", 0);
				$pdf->Line($xs,$hot+4,$xe,$hot+4);
				
				$hot+=$hauteur_ligne/4;
				
				$pdf->SetXY($xt, $hot);
				$pdf->Cell(10,8,"30", 0,0, "R", 0);
				$pdf->Line($xs,$hot+4,$xe,$hot+4);
				
				$hot+=$hauteur_ligne/4;
				
				$pdf->SetXY($xt, $hot);
				$pdf->Cell(10,8,"45", 0,0, "R", 0);
				$pdf->Line($xs,$hot+4,$xe,$hot+4);
			}
		}
		couleur('blanc');
		for($l=$xs-1 ; $l<$xe+1 ; $l+=0.5){
			$pdf->Line($l,30,$l,280);
		}
			
			
		couleur('noir');
		$pdf->Line($d+55+($c*$largeur_colone),11,$d+55+($c*$largeur_colone),24);
		
		if($ro[1]==1){
			$pdf->SetFont('Times','',22);
			couleur('bleu');
			$pdf->RotatedText($d+22+($c*$largeur_colone),150,"FÉRIÉ",45);
		}
	}
	$i++;
	$c++;
	$jour+=$day;
	
}

planning($lan+1);
scol($lan+1);
calendrier($lan+1);

$pdf->Output();
?>