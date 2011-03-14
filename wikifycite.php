<?php
/**
* The Wikify Cite Tool
*  Looks for citations in the $format array, and then turns
*  those citations into wikilinks by adding double brackets.
*
*  Citations are fairly easy to identify, and links are good
*  for a wiki. Run with wikify.html, any user can run text
*  through this function with by copying and pasting into a
*  text area.
*/

$formats = array(
	"([0-9]+) (U.S.) ([0-9]+)", 		// U.S. Reports						U.S. Supreme Court	(1975-present)
	"([0-9]+) (U.S. \([0-9]+ Wall.\)) ([0-9]+)", 		// Wallace (1863-1874)
	"([0-9]+) (U.S. \([0-9]+ Black\)) ([0-9]+)", 		// Black (1861-1862)
	"([0-9]+) (U.S. \([0-9]+ How.\)) ([0-9]+)", 			// Howard (1843-1860)
	"([0-9]+) (U.S. \([0-9]+ Pet.\)) ([0-9]+)", 			// Peters (1828-1842)
	"([0-9]+) (U.S. \([0-9]+ Wheat.\)) ([0-9]+)", 		// Wheaton (1816-1827)
	"([0-9]+) (U.S. \([0-9]+ Cranch\)) ([0-9]+)", 		// Cranch (1801-1815)
	"([0-9]+) (U.S. \([0-9]+ Dallas\)) ([0-9]+)", 		// Dallas (1790-1800)
	"([0-9]+) (F.) ([0-9]+)", 			// Federal Reporter					Circuit Courts
	"([0-9]+) (F.2d) ([0-9]+)", 		// Federal Reporter, Second 		Circuit Courts
	"([0-9]+) (F.3d) ([0-9]+)", 		// Federal Reporter, Third			Circuit Courts
	"([0-9]+) (F. Supp.) ([0-9]+)",		// Federal Supplement 
	"([0-9]+) (F. Supp.2d) ([0-9]+)",	// Federal Supplement, Second
 	"([0-9]+) (B.R.) ([0-9]+)",			// Bankruptcy Reporter

	"([0-9]+) (So.) ([0-9]+)",			// Southern Reporter				Alabama, Louisiana, Mississippi
	"([0-9]+) (So. 2d) ([0-9]+)",		// Southern Reporter, Second		Alabama, Louisiana, Mississippi
	"([0-9]+) (P.) ([0-9]+)",			// Pacific Reporter					Arizona, California, Colorado, Idaho, Kansas, Nevada, New Mexico, Oklahoma, Utah, Washington, Wyoming
	"([0-9]+) (P.2d) ([0-9]+)",			// Pacific Reporter, Second			Alaska, Arizona, California, Colorado, Hawaii, Idaho, Kansas, Navada, New Mexico, Oklahoma, Oregon, Utah, Washington, Wyoming
	"([0-9]+) (P.3d) ([0-9]+)",			// Pacific Reporter, Third			Alaska, Arizona, California, Colorado, Hawaii, Idaho, Kansas, Navada, New Mexico, Oklahoma, Oregon, Utah, Washington, Wyoming
	"([0-9]+) (S.W.) ([0-9]+)",			// Southwestern Reporter			Arkansas, Florida, Kentucky, Tennessee, Texas
	"([0-9]+) (S.W.2d) ([0-9]+)",		// Southwestern Reporter, Second	Arkansas, Florida, Kentucky, Tennessee, Texas
	"([0-9]+) (S.W.3d) ([0-9]+)",		// Southwestern Reporter, Third		Arkansas, Florida, Kentucky, Tennessee, Texas
	"([0-9]+) (A.) ([0-9]+)",			// Atlantic Reporter				Connecticut, Delaware, D.C., Maine, Maryland, New Hampshire, New Jersey, Pennsylvania, Rhode Island, Vermont
	"([0-9]+) (A.2d) ([0-9]+)",			// Atlantic Reporter, Second		Connecticut, Delaware, D.C., Maine, Maryland, New Hampshire, New Jersey, Pennsylvania, Rhode Island, Vermont
	"([0-9]+) (S.E.) ([0-9]+)",			// Southeastern Reporter			Georgia, North Carolina, South Carolina, Virginia, West Virginia
	"([0-9]+) (S.E.2d) ([0-9]+)",		// Southeastern Reporter, Second	Georgia, North Carolina, South Carolina, Virginia, West Virginia
	"([0-9]+) (N.E.) ([0-9]+)",			// Northeastern Reporter			Illinois, Indiana, Massachusetts, New York, Ohio
	"([0-9]+) (N.E.2d) ([0-9]+)",		// Northeastern Reporter, Second	Illinois, Indiana, Massachusetts, New York, Ohio
	"([0-9]+) (N.W.) ([0-9]+)",			// Northwestern Reporter			Iowa, Michigan, Minnesota, Nebraska, North Dakota, South Dakota, Wisconsin
	"([0-9]+) (N.W.2d) ([0-9]+)",		// Northwestern Reporter, Second	Iowa, Michigan, Minnesota, Nebraska, North Dakota, South Dakota, Wisconsin
	
	"()(Ala. Code §) ([0-9]+)", 		// Code of Alabama (Compilation)
	"()(Alaska Stat. §) ([0-9]+)", 		// Alaska Statutes (Compilation)
	"()(Ariz. Rev. Stat. §) ([0-9]+)", 	// Arizona Revised Statutes Annotated (Compilation)
	"()(Ark Code Ann. §) ([0-9]+)", 	// Arkansas Code Annotated (Compilation)
	"()(Cal. Civ. Code §) ([0-9]+)", 	// West's Annotated California Civil Code (Compilation)
	"()(Cal. Civ. Proc. Code §) ([0-9]+)", 	// West's Annotated California Civil Code (Compilation)
	"()(Cal. Bus. & Prof. Code §) ([0-9]+)", 	// West's Annotated California Code of Civil Procedure (Compilation)
	"()(Cal. Evid. Code §) ([0-9]+)", 	// West's Annotated California Business and Professions  Code (Compilation)
	"()(Cal. Penal Code §) ([0-9]+)", 	// West's Annotated California Penal Code (Compilation)
	"()(Cal. Ins. Code §) ([0-9]+)", 	// West's Annotated California Insurance Code (Compilation)
	"()(Cal. Rev. & Tax Code §) ([0-9]+)", 	// West's Annotated California Revenue and Taxation Code (Compilation)
	
										// California code is incomplete... there are a bunch
	"()(Colo. Rev. Stat. §) ([0-9]+)", 	// Colorado Revised Statutes (Compilation)
	"()(Conn. Gen. Stat. §) ([0-9]+)", 	// General Statutes of Connecticut (Compilation)
	"(Del. Code Ann. tit.) ([0-9]+ §) ([0-9]+)", 	// General Statutes of Connecticut (Compilation)
	"()(D.C. Code §) ([0-9]+)", 		// D.C. Official Code (Compilation)
	"()(Fla. Stat. §) ([0-9]+)", 		// Florida Statutes (Compilation)
	"()(Ga. Code Ann. §) ([0-9]+)", 	// Official Code of Georgia Annotated (Compilation)
	"()(Haw. Rev. Stat. §) ([0-9]+)", 	// Hawaii Revised Statutes  (Compilation)
	"()(Idaho Code Ann. §) ([0-9]+)", 	// Idaho Code Annotated (Compilation)
	"([0-9]+) (Ill. Comp. Stat.) ([0-9]+\/[0-9]+)", 	// Illinois Ompiled Statutes (Compilation)
	"([0-9]+) (Ind. Code §) ([0-9]+\/[0-9]+)", 	// Indiana Code (Compilation)
	"([0-9]+) (Iowa Code §) ([0-9]+\/[0-9]+)", 	// Code of Iowa (Compilation)
	"([0-9]+) (Kan. Stat. Ann. §) ([0-9]+\/[0-9]+)", 	// Kansas Statutes Annotated (Compilation)
	"([0-9]+) (Ky. Rev. Stat. Ann. §) ([0-9]+\/[0-9]+)", 	// Kentucky Revised States Annotated (Compilation)
	"()(La. Rev. Stat. Ann. §) ([0-9]+\/[0-9]+)", 	// Louisiana Revised Statutes Annotated (Compilation)
	"([0-9]+) ( §) ([0-9]+\/[0-9]+)", 	// Indiana Code (Compilation)
	"(Me. Rev. Stat. Ann. tit.) ([0-9]+) § ([0-9]+)", 	// Main Revised Statutes Annotated (Compilation)
	"(Mass. Gen. Laws ch.) ([0-9]+\, §) ([0-9]+)", 	// General Laws of Massachusetts (Compilation)
	"()(Mich. Comp. Laws §) ([0-9]+)", 	// Michigan Compiled Laws (Compilation)
	"()(Minn. Stat. §) ([0-9]+)", 		// Minnesota Revised Statutes Annotated (Compilation)
	"()(Miss. Code Ann. §) ([0-9]+)", 	// Mississippi Revised Statutes Annotated (Compilation)
	"()(Mont. Code Ann. §) ([0-9]+)", 	// Montana Revised Statutes Annotated (Compilation)
	"()(Neb. Rev. Stat. §) ([0-9]+)", 	// Nebraska Revised Statutes Annotated (Compilation)
	"()(Nev. Rev. Ann. §) ([0-9]+)", 	// Nevada Revised Statutes Annotated (Compilation)
	"()(N.H. Rev. Stat. Ann. §) ([0-9]+)", 	// New Hampshire Revised Statutes Annotated (Compilation)
	"()(N.J. Stat. Ann. §) ([0-9]+)", 	// New Jersey Statutes Annotated (Compilation)
	"(N.M. Stat. §) ([0-9]+)", 			// New Mexico Statutes Annotated (Compilation)
	"()(N.Y. Penal Law §) ([0-9]+)", 	// McKinney's Consoledateed Laws of New York Annotated (Compilation)
										// New York code is incomplete... there are a bunch
	"(N.C. Gen Stat. §) ([0-9]+)", 		// General Statutes of North Carolina (Compilation)
	"()(N.D. Cent. Code §) ([0-9]+)", 	// North Dakota Century Code (Compilation)
	"()(Ohio Rev. Code Ann. §) ([0-9]+)", 	// Page's Ohio Code Annotated (Compilation)
	"(Okla. Stat. tit.) ([0-9]+) § ([0-9]+)", 	// Oklahoma Revised Statutes Annotated (Compilation)
	"()(Or. Rev. Stat. §) ([0-9\.]+)", 	// Oregon Revised Statutes  (Compilation)
	"([0-9]+) (Pa. Cons. Stat. §) ([0-9]+)", 	// Pennsylvania Consolidated Statutes (Compilation)
	"()(R.I. Gen. Laws §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Rhode Island Revised Statutes Annotated (Compilation)
	"()(S.C. Code Ann. §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Code of Laws of South Carolina (Compilation)
	"()(R.I. Gen. Laws §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Rhode Island Revised Statutes Annotated (Compilation)
	"()(S.D. Codified Laws §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// South Dakota Codified Laws (Compilation)
	"()(R.I. Gen. Laws §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Rhode Island Revised Statutes Annotated (Compilation)
	"()(Tenn. Code Ann. §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Tennessee Code Annotated (Compilation)
	"()(Tex. Penal Code Ann. §) ([0-9]+)", 	// Texas Penal Code Annotated (Compilation)
										// Texas code is incomplete... there are a bunch
	"()(Utah Code Ann. §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Utah Code Annotated (Compilation)
	"(Vt. Stat. Ann. tit.) ([0-9]+ \, §) ([0-9]+)", 	// Vermont Code Annotated (Compilation)
	"()(Va. Code Ann. §) ([0-9]+\-[0-9]+)", 	// Virginia Code Annotated (Compilation)
	"()(Wash. Rev. Code §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Revised Code of Washington (Compilation)
	"()(W. Va. Code §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// West Virginia (Compilation)
	"()(Wis. Stat. §) ([0-9]+\.[0-9]+)", 	// Wisconsin Statutes (Compilation)
	"()(Wyo. Stat. Ann. §) ([0-9]+\-[0-9]+\-[0-9]+)", 	// Wyoming Code Annotated (Compilation)
	
	"([0-9]+) (U.S.C. §) ([0-9]+)", 	// U.S. Code
	"([0-9]+) (U.S.C.A. §) ([0-9]+)",	// U.S. Code Annotated
	"([0-9]+) (C.F.R.) ([0-9]+)",		// Code of Federal Regulations
	);

$constitutionFormats = array(
	"([A-Za-z-]+)( Amendment)",
);
if(isset($_GET["article"])){
	$constitutionFormats[] = "(Article )([0-9]+)";
}	
/**
* Turns text that looks like a citation into a wikilink
*
* @param $txt string Text that contains something that looks like a citation
* @param $formats array List of regex formats for citations
* @return array [0] => Text with wikified citations, [1] => Array of wikified citations (for
* use identifing short cites), [2] => Each match against a list in the $formats array
*/
function wikifyCite($txt,$formats) {
	global $formats, $constitutionFormats;
	// fix some standard citation formatting errors F. Supp.2d
	$txt = str_replace("F. Supp. 2d","F. Supp.2d",$txt);
	$txt = str_replace("S. C","S.C.",$txt);
	$txt = preg_replace("/§([0-9]+)/","§ $1",$txt);
	$txt = str_replace("U. S.","U.S.",$txt);
	$txt = str_replace("U.S. C.","U.S.C.",$txt);
	$txt = str_replace("F. 3d","F.3d",$txt);
	$txt = str_replace("F. 2d","F.3d",$txt);
	$txt = str_replace("A. 2d","A.3d",$txt);
	
	$txt = preg_replace("/\\\\([\'\"]+)/","$1",$txt);
	$txt = str_replace("<","&lt;",$txt);
	foreach($formats as $format){
		preg_match_all ("/".$format."/",$txt, $matchesall, PREG_SET_ORDER);
		if(is_array($matchesall)){ 
			foreach ($matchesall as $matches) {
				$list[] = array($matches[1],$matches[2],$matches[3]);
				if(preg_match("/\[\[".$format."\]\]/",$txt, $matchelse)){} else {
					$txt = preg_replace("/".$format."/","[[$1 $2 $3]]",$txt);
					$txt = str_replace("[[ ","[[",$txt);
				}		
			}
		}
	}
	/**
	foreach($constitutionFormats as $constitutionFormat) {
		preg_match_all("/".$constitutionFormat."/",$txt,$constitutionMatches, PREG_SET_ORDER);
		if(is_array($constitutionMatches)){
			foreach($constitutionMatches as $match){
				if($match[1] == "Article "){
					if(preg_match("/\[\[".$$constitutionFormat."\]\]/",$txt)) {} else {
						$txt = preg_replace("/".$constitutionFormat."/","[[Constitution of the United States of America#Article $2]]",$txt);
					}
				}
				
				if($match[2] == " Amendment"){
					if(preg_match("/Amendment \]\]/",$txt)) {} else {
									$matchLower = strtolower($match[1]);
				switch($matchLower){
					case "first":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "second":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "third":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "fourth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "fifth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "sixth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "seventh":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "eighth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "ninth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "tenth":
						$constitutionCite = "United States Bill of Rights";
						break;
					case "eleventh":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XI";
						break;
					case "twelfth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XII";
						break;
					case "thirteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XIII";
						break;
					case "fourteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XIV";
						break;
					case "fifteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XV";
						break;
					case "sixteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XVI";
						break;
					case "seventeenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XVII";
						break;
					case "eighteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XVIII";
						break;
					case "ninteenth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XIX";
						break;
					case "twentieth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XX";
						break;
					case "twenty-first":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXI";
						break;
					case "twenty-second":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXII";
						break;
					case "twenty-third":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXIII";
						break;
					case "twenty-fourth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXIV";
						break;
					case "twenty-fifth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXV";
						break;
					case "twenty-sixth":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXVI";
						break;
					case "twenty-seventh":
						$constitutionCite = "Additional amendments to the United States Constitution#Amendment XXVII";
						break;
						
				}
						$txt = preg_replace("/".$match[1].$match[2]."/","[[".$constitutionCite."|".$match[1]." Amendment]]",$txt);
					}
				}
			
			}
		}	
	}	
	*/
	if(isset($list)){
		foreach($list as $shortcite) {
			if(preg_match("/\[\[".$shortcite[0]." ".$shortcite[1]." ".$shortcite[1]."|".$shortcite[0]." ".$shortcite[1].", at ([0-9]+)\]\]/",$txt)){} else {
				if(preg_match("/".$shortcite[0]." ".$shortcite[1].", at ([0-9]+)/",$txt,$matches)) {
					$txt = preg_replace("/(".$shortcite[0].") (".$shortcite[1].",) at ([0-9]+)/","[[".$shortcite[0]." ".$shortcite[1]." ".$shortcite[2]."|$1 $2 at $3]]",$txt);
				}
			}
		}
	}
	if(!isset($list)){$list="";}
	return array($txt, $list, $matchesall);	
}
if(isset($_POST['text'])) {
	$result = wikifyCite($_POST['text'],$formats);

	print $result[0];
}
?>