<?php
require_once('opendatabase.php');
//inscription du score gagnant
$score_gagnant = $_POST['chronotime'];
$NbCoup = $_POST['nb_coup'];
if (isset($score_gagnant)) {
    echo "Votre dernier temps : ".$score_gagnant. " (en ".$NbCoup." coups)";
    $sql = "INSERT INTO Oclock (Nb_Coup, Duree_jeu, Date_jeu) VALUES ('$NbCoup', '$score_gagnant',curdate())";
    $conn->query($sql);
}

//Récupération des 3 meilleurs scores
$sql = "SELECT * FROM Oclock ORDER BY Duree_jeu limit 3";
$req=$conn->query($sql);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Memory</title>
	<link rel="stylesheet" href="style.css" />

</head>
<body>
<div id="page">
<h1>Jeu du mémory</h1>
<p id="cmt"></p>
	<br>Meilleurs temps : <?php while($data = $req->fetch_assoc()) { echo "<br>".$data['Duree_jeu']." (".$data['Nb_Coup']." coups)"; } ?>

					   
  <p>&nbsp;</p>
  <div id="jeu"></div>
<form name="chronoForm" method="post" action="<?php $_SERVER['PHP_SELF']?>">
  
    <input type="text" name="chronotime" id="chronotime" value="0:00:00:00" />
    <input type="hidden" name="nb_coup" id="nb_coup" />
    <p align="center">&nbsp;<div id="progressbar">
<div id="indicator"><div id="progressnum">0</div></div>
</div>
</p>

<a href="<?php $_SERVER['PHP_SELF']?>" title="Commencer un nouveau jeu">Nouvelle partie</a>
</form>


								

</div>
<script>

// barre progression
var maxprogress = 12;   // total à atteindre
var actualprogress = 0;  // valeur courante
var itv = 0;  // id pour setinterval
function prog()
{
  if(actualprogress >= maxprogress) 
  {
    clearInterval(itv);   	
    return;
  }	
  var progressnum = document.getElementById("progressnum");
  var indicator = document.getElementById("indicator");
  actualprogress += 1;	
  indicator.style.width=600/12*actualprogress + "px";
  progressnum.innerHTML = actualprogress;
  if(actualprogress == maxprogress) clearInterval(itv);   
}
											 
															   
//chronomètre
var startTime = 0
var start = 0
var end = 0
var diff = 0
var timerID = 0
window.onload = chronoStart;
function chrono(){
	end = new Date()
	diff = end - start
	diff = new Date(diff)
	var msec = diff.getMilliseconds()
	var sec = diff.getSeconds()
	var min = diff.getMinutes()
	var hr = diff.getHours()-1
	if (min < 10){
		min = "0" + min
	}
	if (sec < 10){
		sec = "0" + sec
	}
	if(msec < 10){
		msec = "00" +msec
	}
	else if(msec < 100){
		msec = "0" +msec
	}
	document.getElementById("chronotime").value = hr + ":" + min + ":" + sec + ":" + msec
	timerID = setTimeout("chrono()", 10)
}
function chronoStart(){
	document.chronoForm.startstop.value = "stop!"
	document.chronoForm.startstop.onclick = chronoStop
	document.chronoForm.reset.onclick = chronoReset
	start = new Date()
	chrono()
}

function chronoReset(){
	document.getElementById("chronotime").value = "0:00:00:000"
	start = new Date()
}
function chronoStopReset(){
	document.getElementById("chronotime").value = "0:00:00:000"
	document.chronoForm.startstop.onclick = chronoStart
}
function chronoStop(){
	document.chronoForm.startstop.value = "start!"
	document.chronoForm.startstop.onclick = chronoContinue
	document.chronoForm.reset.onclick = chronoStopReset
	clearTimeout(timerID)
}
												  
												  
/////jeu												  
function $(o){return document.getElementById(o);}
var mmr=new function(){
    // Configurer le jeu
    this.nbi=12,//Nombre d'images disponibles numérotées de 1 à nbi (0 est réservé pour le dos de carte)
    this.ipr='images/',// Préfixe des images avec chemin 
    this.isf='.jpg',// Suffixe des images avec le . (le numéro, de 1 à nbi, viendra s'intercaler)
    this.nbl=6,this.pxl=90,// Nombre de cartes en largeur et taille en pixels
    this.nbh=4,this.pxh=100,// Nombre de cartes en hauteur et taille en pixel
															   
    // Variables et fonctions internes
    this.jeu,// Tableau des numéros d'images indexés par leur position
  	this.nbr,// Nombre de retournements efféctués
  	this.pir,// Booléen image retournée
  	this.dbl,// Objet, doublet des deux dernières images retournées
  	this.stt,// Show (setTimeout) en cours
	this.points=0,// Nombre de paires trouvées
    
	// Initialiser
    this.ini=function(){var i,nbp=this.nbl*this.nbh,nbj=nbp>>1,chj='';
      
      $('jeu').style.width=this.nbl*this.pxl+'px';$('jeu').style.height=this.nbh*this.pxh+'px';
       
	 // Afficher le jeu (des dos de cartes seulement les numéros sur les identifiants seront suffisants pour déterminer les images)
        for (i=0;i<nbp;i++) chj+='<img id=c'+i+' src="'+this.ipr+'0'+this.isf+'" onclick="mmr.shw(this)">';
        $('jeu').innerHTML=chj;
        // Aucun retournement, pas de première image à consulter, pas de doublet ni de setTimeout en cours
        this.nbr=0;this.pir=0;this.dbl={},this.stt=null;
        $('cmt').innerHTML="Retournez deux cartes identiques avec deux clics,<br>Toutes les cartes, avec le minimum de clics en un  minimum de temps !<br>Vous avez 30 essais (60 retournements)";
        // choix de nbj cartes parmi nbrImg
        var j,k,imd=this.nbi,imt=0,ims={},jms=[];// Nombres images disponibles et images tirées, images tirées
        do {k=1+Math.floor(Math.random()*(imd-imt));// Tirage un rang parmi les cartes restantes
          // Décrementer k pour chaque image disponible pour prendre la kième, lorsque k s'annule
            for (i=1;;i++) if (typeof(ims[i])!='number' && (--k)==0) {ims[i]=1;break;} imt++}
        while (imt!=nbj);
        // Dupliquer les images tirées dans un tableau et mélanger le tout 7 fois pour définir le jeu.
        j=0;for (i in ims) {jms[j++]=i;jms[j++]=i;}
        j=7;while (j--) jms.sort(function(){return 0.5-Math.random()});this.jeu=jms;
    
    },
  // Montrer la carte cliquée
    this.shw=function(o){var c=c=parseInt(o.id.substr(1)),i=this.jeu[c];
      // Ne rien faire si timeout en cours ou image déjà retournée (pas un dos)
        if (this.stt || -1==o.src.indexOf(this.ipr+0)) return
        // On retourne en affichant l'image enregistre le clic et l'identifiant de l'image dans le doublet
        o.src=this.ipr+i+this.isf;this.nbr++;this.dbl[o.id]=1;
        $('cmt').innerHTML='<br>Nombre de retournements effectués '+this.nbr;
		if(this.nbr==1) chronoReset();chrono();
		//pour tests : if(this.nbr==4) this.ggn();
		if (this.nbr==60) this.prd();
        // Si première image retournée on le note et retour
        if (!this.pir) {this.pir=i;return}
        // Sinon si un vrai couple on réinitialize
        if (this.pir==i) {
			mmr.stt=mmr.pir=0;mmr.dbl={};
			this.points++;
			prog();
			$('cmt').innerHTML='<br>Nombre de retournements effectués '+this.nbr+ ' / Nombre de paires trouvées '+this.points;
            if (this.points==12) this.ggn();
			return;
		}
        // Lancer l'affichage minuté
        this.stt=setTimeout(mmr.hdd,1000);},
    	this.hdd=function(){mmr.stt=mmr.pir=0;
        for (var x in mmr.dbl) $(x).src=mmr.ipr+'0'+mmr.isf;mmr.dbl={}},
    
		this.ggn=function(){
		  var score= document.getElementById('chronotime').value;
          document.getElementById('nb_coup').value = this.nbr;
          alert("Vous avez gagné en "+score+" et "+this.nbr+" coups");
          chronotime.parentElement.submit();
		}
		this.prd=function(){alert("Vous-avez perdu !")}
}
	

mmr.ini()
</script>
</body>
</html>