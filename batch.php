<?php

# This is a tool for batch processing United States Supreme Court
# cases
#
# Last updated 8/15/2010

require_once("config.php");
require_once("citations.php");
require_once("wikifycite.php");
require_once("authorbyname.php");
require_once("catlist.php");

/**
* Check if a wikipediaor wikisource article exists for a given page title
*  
* @param $title string Title of the page you want to check
* @param $wiki string Either "wikipedia" 
* @return string If exists, {{subst:BASEPAGENAME}}, otherwise no
*/
function wikiCheckPageExistence($title, $wiki){
	$title = str_replace(" ","_",$title);
	switch($wiki) {
		case "wikipedia":
			$url = "http://en.wikipedia.org/w/api.php?action=query&titles=".$title."&format=php";
			break;
		case "wikisource":
			$url = "http://en.wikisource.org/w/api.php?action=query&titles=".$title."&format=php";
			break;
	}
	$ch = curl_init();
	$timeout = 7;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: wikiCheckPageExistence/1 stephen@thjnk.com');
	if(curl_exec($ch) == false) {
		print "\nError: Unable to connect to ".$wiki."\n";
	}else{
		$data = curl_exec($ch);
		curl_close($ch);
		
		$data = unserialize($data);
		
		$arr = array_keys($data["query"]["pages"]);
			
		if($arr[0] >= 1) {
			$page = "{{subst:BASEPAGENAME}}"; // want to set the wikipedia link to anything?
		} else {
			$page = "no";	
		}
		return $page;
	}
}

/**
* Split a citation into volume, reporter, and page
*
* @param $fullCitation string Full citation
* @return array ["Volume"], ["Reporter"], ["Page"]
*/
function explodeCaseName($FirstCitation = "none", $SecondCitation = "none", $ThirdCitation = "none"){
	global $reporter;
	$citations = array ($FirstCitation, $SecondCitation, $ThirdCitation);
	foreach($citations as $fullCitation) {
		$explodedCitation = explode(" ",$fullCitation);
		foreach($explodedCitation as $key => $citationPart){
			if($citationPart==$reporter){
				$citation["Volume"] = $explodedCitation[$key-1];
				$citation["Reporter"] = $explodedCitation[$key];
				$citation["Page"] = $explodedCitation[$key+1];
			}
		}
	}
	return $citation;
}

/**
* Trim the left side of each line
*
* @param $txt string Text to be trimmed
* @return string Now you don't have to worry about wiki-indentations
*/
function trimLine($txt){
	$lines = explode("\n",$txt);
	foreach($lines as $k=>$line){
		$lines[$k] = ltrim($line);
	}
	
	return implode("\n",$lines);
}

/**
* Check a case's year against the author's year on court
*
* Necessary to determine the author when you only know the last name and there 
* are multiple authors with the same last name
*
* @param $year string Year of the case
* @param $year string Name of the author
* @return string The true author
*/
function multiAuthorName($year, $name){
	if(is_array($name)){
 
		$upper = $name["1_end"];
		$lower = $name["1_start"];
 
		if($upper > $year && $lower < $year) {
			$caseAuthor = $name[1];
		} else {
			$caseAuthor = $name[0];
		}
 
	} else {
 		$caseAuthor = $name;
	}
	return $caseAuthor;
}

/**
* Apply categories by matching words or phrases in the syllabus
* 
* @param $txt string
* @param $list string 
* @return array Categories
*/
function categoryGuess($txt, $list){

	$categories = array();

	foreach($list as $key => $cat) {
	
		if(preg_match("/$key/",$txt)){
			if(!in_array($cat,$categories)) {
				$categories[] = $cat;
			}
		}
	
	}
	
	if($categories == array()){
		$categories[] = "[[Category:Uncategorized United States Supreme Court decision]]";
	} else {
		$categories[] = "[[Category:Automated categorization]]";
	}
	
	return $categories;
}

/**
* Split the case name into the parties' names
*
* @param $unformattedName string Full name
* @return array [0] => full name, [1] => first party
* [2] => second party
*/
function formatCaseName($unformattedName){
	$unformattedname = str_replace("Mc","MC",$unformattedName);
	$splitPartyName = explode("v.", $unformattedName);
	$party = $splitPartyName;
	
	$party[0] = trim(ucwords(strtolower(preg_replace("/[a-z]|\,|((!?[A-Z])[a-z])|\.|([A-Z]\.[A-Z]\.)|([A-Z]\.\s)/","",$splitPartyName[0]))));
	$party[1] = trim(ucwords(strtolower(preg_replace("/[a-z]|\,|((!?[A-Z])[a-z])|\.|([A-Z]\.[A-Z]\.)|([A-Z]\.\s)/","",$splitPartyName[1]))));
	
	$party[0] = trim(str_replace("/","",$party[0]));
	$party[1] = trim(str_replace("/","",$party[1]));
	$party[1] = str_replace(">>","",$party[1]);
	$name = $party[0]." v. ".$party[1];
	$name = str_replace("  "," ",$name);
	$name = rtrim($name);
	return array($name, $splitPartyName[0], $splitPartyName[1]);
}

function removeOddCharacters($txt){
	$txt = trim($txt);
	$txt = str_replace("Â	"," ",$txt);
	$txt = str_replace("Â§","§",$txt);
	$txt = str_replace("â€™","'",$txt);
	$txt = str_replace("â€œ","\"",$txt);
	$txt = str_replace("â€","\"",$txt);
	$txt = str_replace("''","\"",$txt);
	$txt = str_replace("        ","",$txt);
	$txt = str_replace("â€”","-",$txt);
	$txt = str_replace("&#x2014;","-",$txt);
	
	return $txt;
}

function strstr_after($haystack, $needle, $case_insensitive = false) {
	$strpos = ($case_insensitive) ? 'stripos' : 'strpos';
	$pos = $strpos($haystack, $needle);
	if (is_int($pos)) {
		return substr($haystack, $pos + strlen($needle));
	}
	return $pos;
}

/**
* Match footnotes and return them as a string 
*
* @param $paragraph string A paragraph of text
* @param $arr array Footnotes
* @return string Group of footnotes
*/

function opinionFootnotesFormat($paragraph,$arr){
	if(!isset($footnoteGroup)) { $footnoteGroup = ""; }
	preg_match_all("/([#-_a-z0-9]*) \<ref\> ([0-9\*]+) \<\/ref\>/",$paragraph,$matches, PREG_SET_ORDER);
		foreach($matches as $match){
			$foundFoot = $arr[$match[1]."_ref"][1];
			$footnoteGroup = $footnoteGroup . "\n" . $foundFoot;
		}
	
	return $footnoteGroup;
}

/**
* preg_replace to change to footnote template
*/
function opinionParagraphRefs($paragraph){
	return preg_replace("/([#-_a-z0-9]*) \<ref\> (\<nowiki\>)*([0-9\*]+)(\<\/nowiki\>)* \<\/ref\>/","{{ref|$3}}",$paragraph);
}


/**
* puts together the page for each opinion
* 
*/
function buildPage($Section, $caseName, $authorLastName, $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $body, $ParallelCites) {
	
	$USSCcaseNo = "2"; // ever page except syllabus uses {{USSCcase2}}
	
	switch($Section){
		case "Syllabus":
			$pageTitle = $caseName[0];
			$ParallelCiteString = $ParallelCites[0].$ParallelCites[1];
			$USSCcaseNo = ""; // no number for syllabus; otherwise, it's "2"
			break;
		case "Opinion of the Court":
			$pageTitle = $caseName[0]."/Opinion of the Court";
			/**
			* Prevents ParallelCite and Categories from being printed on
			* this subpage.
			*/
			$ParallelCiteString = "";
			$body["Cats"] = "";
			break;
		case "Dissent":
			$pageTitle = $caseName[0]."/Dissent ".$authorLastName;
			$ParallelCiteString = "";
			$body["Cats"] = "";
			break;
		case "Concurrence":
			$pageTitle = $caseName[0]."/Concurrence ".$authorLastName;
			$ParallelCiteString = "";
			$body["Cats"] = "";
			break;
		default:
			$pageTitle = $caseName[0]."/Opinion ".$authorLastName;
			$ParallelCiteString = "";
			$body["Cats"] = "";
			break;
	}
	
	$page = "{{-start-}}
	'''".$pageTitle."'''
	".$ParallelCiteString."
	{{header
	| title    = {{subst:PAGENAME}}
	| author   = ".$TemplateHeaderValues["AuthorFullName"]." 
	| section  = ".$Section." 
	| previous = [[wikisource:Supreme Court of the United States|United States Supreme Court]]
	| next     = 
	| notes    = 
	}}
	{{USSCcase".$USSCcaseNo."
	|percuriam                   = ".$TemplateUSSCcaseValues["perCuriam"]."
	|concurrence_author1         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][0]." 
	|concurrence_author2         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][1]." 
	|concurrence_author3         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][2]." 
	|concurrence_author4         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][3]."  
	|concurrence_author5         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][4]."  
	|concurrence_author6         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][5]." 
	|concurrence_author7         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][6]." 
	|concurrence_author8         = ".$TemplateUSSCcaseValues["concurrenceAuthorLastName"][7]."  
	|concurrence-dissent_author1 =
	|concurrence-dissent_author2 =
	|concurrence-dissent_author3 =
	|concurrence-dissent_author4 =
	|dissent_author1             = ".$TemplateUSSCcaseValues["dissentAuthorLastName"][0]."  
	|dissent_author2             = ".$TemplateUSSCcaseValues["dissentAuthorLastName"][1]."   
	|dissent_author3             = ".$TemplateUSSCcaseValues["dissentAuthorLastName"][2]."   
	|dissent_author4             = ".$TemplateUSSCcaseValues["dissentAuthorLastName"][3]." 
	|separate_author1            =
	|separate_author2            =
	|separate_author3            =
	|separate_author4            =
	|linked_cases                = 
	|wikipedia					 = ".$TemplateUSSCcaseValues["wpiwl"]." 
	}}
	{{CaseCaption   
	| court = ".$TemplateCaseCaptionValues["court"]." 
	| volume = ".$TemplateCaseCaptionValues["arrCite"]['Volume']." 
	| reporter = ".$TemplateCaseCaptionValues["arrCite"]['Reporter']."  
	| page = ".$TemplateCaseCaptionValues["arrCite"]['Page']." 
	| party1 = ".$TemplateCaseCaptionValues["name"][1]." 
	| party2 = ".$TemplateCaseCaptionValues["name"][2]."  
	| casename = ".$TemplateCaseCaptionValues["name"][0]." 
	| lowercourt = ".$TemplateCaseCaptionValues["courtbelow"]." 
	| argued = ".$TemplateCaseCaptionValues["argdate"]." 
	| decided = ".$TemplateCaseCaptionValues["decdate"]." 
	| case no = ".$TemplateCaseCaptionValues["docket"]." 
	}}
	<div class='courtopinion'>
	".$body["mainText"]." 
	
	==Notes==
	".$body["notes"]." 
	</div>
	".$body["Cats"]."
	{{PD-USGov}}
	{{-stop-}}";
	
	return $page;
}

function buildTalkPage($Section, $name, $authorLastName, $contributor, $decdate, $arrCite) {
	switch($Section) {
		case "Syllabus":
			$talkpageTitle = "Talk:".$name[0];
			break;
		case "Opinion of the Court":
			$talkpageTitle = "Talk:".$name[0]."/Opinion of the Court";
			break;
		case "Dissent":
			$talkpageTitle = "Talk:".$name[0]."/Dissent ".$authorLastName;
			break;
		case "Concurrence":
			$talkpageTitle = "Talk:".$name[0]."/Concurrence ".$authorLastName;
			break;
		default:
			$talkpageTitle = "Talk:".$name[0]."/Opinion ".$authorLastName;
			break;
			
	}

	$talkpage = "{{-start-}}
	'''".$talkpageTitle."'''
	{{Template:WikiProject USSC}}
	{{textinfo
	| edition      = ''".$name[0]."'', ".$decdate."  .
	| source       = ''".$name[0]." '' from http://bulk.resource.org/courts.gov/c/US/".$arrCite['Volume']." 
	| contributors = ".$contributor."
	| progress     = Text being edited [[Image:25%.png]]
	| notes        = Gathered and wikified using an automated tool. See this [[user:slaporte/slaw|documentation]] for more information.
	| proofreaders = 
	}}
	{{-stop-}}";
	
	return $talkpage;
}

/**
* Main function
* 
* @param $url string URL of the case to process
*/
function batchWikify($url) {
	
	global $contributor, $xpathCourt, $xpathTitle, $xpathName, $xpathAuthor, $xpathCasecite, $xpathCite2, $xpathCite3, $xpathCourtbelow, $xpathDate1, $xpathDate2, $xpathDocket, $xpathParty1, $xpathParty2, $xpathFoots, $xpathParagraph, $xpathSyllabus, $namesVol, $dupList, $reporter, $authorByName, $catlist;
	
	$dissent = 0;
	$dissentNo = 1;
	$dissentAuthor = array();
	$concurrenceAuthor = array();
	$perCuriam = false;
	$Syllabus = $OpinionOfCourt = $missing = $notices = $courtbelow = $arr = "";
	$concurrenceCount = false;
	$dissentCount = false;
	$ConcurText = array();
	$lastNameArray = $lastNamed = "";
	$SyllabusNotes = $Syllabus = "";
	$Con = $Con2 = $Con3 = $Con4 = $Con5 = $Con6 = $Con7 = $Con8 = false;
	$Dis = $Dis2 = $Dis3 = $Dis4 = false;
	$date1 = $date2 = $docket = false;
	$OpinionOfCourt_Notes = $DisNotes = $Dis2Notes = $Dis3Notes = $Dis4Notes = "";
	$ConNotes = $Con2Notes = $Con3Notes = $Con3Notes = $Con4Notes = $Con5Notes = $Con6Notes = $Con7Notes = $Con8Notes = "";
	$missingDissent = $missingConcurrence = "";
	
	/**
	* array of justices with their full name, and years for those justices that share their last name
	*/
	
	$citebank = array();
	 
	$target_url = $url;
	$URLserAgent = '';
	
	/**
	* make the cURL request to $target_url
	*/
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $URLserAgent);
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	if(curl_exec($ch) == false){
		print "\nError: unable to open ".$url."\n";
		return;
	}else{
		$html= curl_exec($ch);
		if (!$html) {
			print "<br />cURL error number:" .curl_errno($ch);
			print "<br />cURL error:" . curl_error($ch);
			exit;
		}
	}
	
	/**
	* Make adjustment to html
	*/
	
	$html = removeOddCharacters($html);
	$html = preg_replace('/<a class="footnote" href="([^"]*)" [^>]*>([0-9a-z\*]+)<\/a>/',' $1 &lt;ref&gt; $2 &lt;/ref&gt;',$html);
	$html = preg_replace('/<a class="footnote" href="([^"]*)">([0-9a-z\*]+)<\/a>/',' $1 &lt;ref&gt; $2 &lt;/ref&gt;',$html);
	$html = str_replace('<p>',"\n",$html);
	$html = str_replace('<p class="indent">',"<p class='indent'> \n",$html);
	$html = str_replace('It is so ordered.',"It is so ordered. \n",$html);
	$html = str_replace('U. S. C.','U.S.C.',$html);
	$html = str_replace('U. S.','U.S.',$html);
	$html = str_replace('ordered. 
	</i>   Justice ','ordered.</i></p></div><div class="num"><p class="indent">Justice ',$html);
	//$html = preg_replace("/<i>(.*)<\/i>/","''$1''",$html);
	$html = str_replace("<i>","''",$html);
	$html = str_replace("</i>","''",$html);
	
	/**
	* parse the html into DOMDocument
	*/
	$caseDOM = new DOMDocument();
	@$caseDOM->loadHTML($html);
	
	$xpath = new DOMXPath($caseDOM);
	 
	$pg_title = $xpath->query($xpathTitle)->item(0)->nodeValue;
	
	/**
	*	gather info from xpath
	*/
	
	isset($xpath->query($xpathCourt)->item(0)->nodeValue) ? $court = $xpath->query($xpathCourt)->item(0)->nodeValue : "";
	isset($xpath->query($xpathName)->item(0)->nodeValue) ? $name = $xpath->query($xpathName)->item(0)->nodeValue : "";
	isset($xpath->query($xpathAuthor)->item(0)->nodeValue) ? $author = $xpath->query($xpathAuthor)->item(0)->nodeValue : "";
	isset($xpath->query($xpathCasecite)->item(0)->nodeValue) ? $casecite = $xpath->query($xpathCasecite)->item(0)->nodeValue : "";
	isset($xpath->query($xpathCourtbelow)->item(0)->nodeValue) ? $courtbelow = $xpath->query($xpathCourtbelow)->item(0)->nodeValue : "";
	isset($xpath->query($xpathDate1)->item(1)->nodeValue) ? $date1 = $xpath->query($xpathDate1)->item(1)->nodeValue : "";
	isset($xpath->query($xpathDate2)->item(0)->nodeValue) ? $date2 = $xpath->query($xpathDate2)->item(0)->nodeValue : "";
	isset($xpath->query($xpathDocket)->item(0)->nodeValue) ? $docket = $xpath->query($xpathDocket)->item(0)->nodeValue : "";
	isset($xpath->query($xpathParty1)->item(0)->nodeValue) ? $party1 = $xpath->query($xpathParty1)->item(0)->nodeValue : "";
	isset($xpath->query($xpathParty2)->item(0)->nodeValue) ? $party2 = $xpath->query($xpathParty2)->item(0)->nodeValue : "";
	isset($xpath->query($xpathCite2)->item(1)->nodeValue) ? $casecite2 = $xpath->query($xpathCite2)->item(1)->nodeValue : "";
	isset($xpath->query($xpathCite3)->item(2)->nodeValue) ? $casecite3 = $xpath->query($xpathCite3)->item(2)->nodeValue : "";
	 
	$decdate = strstr_after($date1, "ed");
	if(!isset($decdate)){
		$decdate = "";
	}
	$argdate = strstr_after($date2, "ed");
	if(!isset($argdate)){
		$argdate = "";
	}
	/**
	* year?
	*/
	
	$year = str_replace(".","",trim(strstr($date2,","),", "));
	 
	if(!is_numeric($year)){
		$year = trim(strstr($date1,","),", ");
	}
	
	 
	$arrCite = explodeCaseName($casecite, $casecite2, $casecite3);
	
	/**
	*  determine file name
	*/
	
	if(preg_match("/([0-9]{3})\.html/",$url,$match)) {
		$caseno = preg_replace("/(^000)|(^00)|(^0)|/","",$match[1]);
		
	}else{
		$caseno = false;
	}
	
	/**
	* Get the case name
	*  - By default, the case is named according to caseName() 
	*  - If available, it will take a case name from a formatted list:
	*      --Save an array of case names with citations as ./input/[volume number].php 
	*      --Name the (case) input files sequentially
	*      --If the file number and citation match, it will use the name from the list
	*
	*     This is useful for importing volumes at a time, especially if you want to check
	*     the case names against a list.
	*  - $completeName preserves the name from the source file. Unfortunately, it is often
	*    too poorly formatted to use...
	*/
	$completeName = formatCaseName($name);
	if(file_exists("./input/".$arrCite['Volume'].".php")){
		require_once("./input/".$arrCite['Volume'].".php");
		if($caseno == false){
			$name = formatCaseName($name);
		} else {
			$caseno = $caseno - 1;
			$casearray = $namesVol[$arrCite['Volume']."-".$caseno];
			if($casearray[1] == $arrCite['Volume']." ".$arrCite['Reporter']." ".$arrCite['Page']." "){
				$name = $casearray;
				$name[3] = $name[1];
				$splitCaseName = explode("v.", $name[0]);
				$name[1] = $splitCaseName[0];
				$name[2] = $splitCaseName[1];
			} else {
				$name = formatCaseName($name);
			}
		}
	} else {
		$name = formatCaseName($name);
	}
	
	/**
	*  check if a case with that name is already up
	*/
	if(wikiCheckPageExistence($name[0], "wikisource") == "{{subst:BASEPAGENAME}}"){
	$name[0] = $name[0]." (".$arrCite['Volume']." ".$arrCite['Reporter']."*** ".$arrCite['Page'].")";
	$dupList[] = $name[0]." (".$arrCite['Volume']." ".$arrCite['Reporter']."*** ".$arrCite['Page'].")";
	}
	
	/**
	*  gather the footnote text
	*/
	
	foreach ($xpath->query($xpathFoots) as $foot){
		if(trim($foot->nodeValue) != "Notes:"){
			if(preg_match ("/([#-_a-z0-9]*) \<ref\> ([0-9a-z\*]+) \<\/ref\>/",$foot->nodeValue,$match)) {
				if(!isset($arr["$match[1]"])) { 
					$arr["$match[1]"] = array($match[2], ltrim(preg_replace("/([#-_a-z0-9]*) \<ref\> ([0-9a-z\*]+) \<\/ref\>[\n\t]*/","\n{{note|$2|$2}}",$foot->nodeValue)));
				}
			}
		}
	}
	
	if($arrCite["Reporter"] == "U.S.") {
		$court = "United States Supreme Court";
	}
	
	/**
	* 	gather syllabus
	*/
	
	$txts = $xpath->query($xpathSyllabus);
	foreach ($txts as $txt) {
		$syll = trim($txt->nodeValue);
		$p1 = $name[1];
		$p2 = $name[2];
		if(isset($name[1][0])){
			preg_match("/([#-_a-z0-9]*) Ref ([0-9\*]+) Ref/",$p1,$matches);
			if(isset($matches[1])){
				$name[1][0] = trim(preg_replace("/([#-_a-z0-9]*) Ref ([0-9a-z\*]+) Ref/","\n{{note|$2|$2}}",$p1));
				$offset = $matches[1]."_ref";
				if(is_array($arr[$offset])){
					$foundFoot = $arr[$offset][1];
					$SyllabusNotes = $SyllabusNotes . "\n" . ltrim($foundFoot);
				}
			}
		}
		if(isset($name[1][1])){
			preg_match("/([#-_a-z0-9]*) Ref ([0-9a-z\*]+) Ref/",$p2,$matches);
			if(isset($matches)){
				//$name[1][1] = trim(preg_replace("/([#-_a-z0-9]*) Ref ([0-9a-z\*]+) Ref/","\n{{note|$2|$2}}",$p2));
				if(isset($matches[1])){
					$offset = $matches[1]."_ref";
					if(isset($arr[$offset])){
						$foundFoot = $arr[$matches[1]."_ref"][1];
					}
					$SyllabusNotes = $SyllabusNotes . "\n" . ltrim($foundFoot);
				}
			}
		}
		preg_match_all("/([#-_a-z0-9]*) \<ref\> ([0-9a-z\*]+) \<\/ref\>/",$syll,$matches, PREG_SET_ORDER);
		foreach($matches as $match){
			$foundFoot = $arr[$match[1]."_ref"][1];
			$syll = trim(preg_replace("/([#-_a-z0-9]*) \<ref\> ([0-9a-z\*]+) \<\/ref\>/","{{ref|$2}}",$syll));
			$SyllabusNotes = $SyllabusNotes . "\n" . ltrim($foundFoot);
		}
		$Syllabus = $Syllabus . $syll;
	}
	
	foreach(categoryGuess($Syllabus,$catlist) as $cat) {
		if(!isset($Cats)){
			$Cats = "";
		}
		$Cats = $Cats . $cat . " ";
	}
	$txts = $xpath->query($xpathParagraph);
	$cite_list = array();
	
	
	/**
	* 	gather opinions
	*
	* Opinions are split into the Opinion of the Court, Dissents, and Concurrences
	* according to regular expressions. The phrases are pretty consistent, but
	* unfortunately not all the same.
	*  Concurrences:
	*   ^justice ([a-z']+)\, concurring
	*   ^justice ([a-z']+)\, with .* concurring( in the judgment)*
	*  Dissents:
	*   ^mr\. justice ([a-z]+), with .* dissenting
	*   ^justice ([a-z']+)\, dissenting\.
	*   ^justice ([a-z']+),* with .* dissenting\.
	*   ^justice ([a-z]+)\, joined .* dissenting\.$
	*   
	*/
	
	foreach ($txts as $txt) {
		$paragraph = $txt->nodeValue;
		/**
		* get rid of paragraph numbers
		*/
		$paragraph = preg_replace("/[0-9]+\n/","",$paragraph);
		$paragraph = preg_replace("/[0-9]+\n\n/","",$paragraph);
		$paragraphlower = strtolower($paragraph);
		$paragraphLowerCase = strtolower($paragraph);
		
		/**
		* clean up potential miswikification
		*/
		$paragraph = preg_replace('/[\n]*\*/','<nowiki>*</nowiki>',$paragraph);
				
		/**
		* Count each concurrence by matching the start of each line against
		* an expression in $concurrenceExpressionArray
		*
		* in a nine person court, there may be up to eight concurrences...
		*/
		$concurrenceExpressionsArray = array(
			"/^justice ([a-z']+)\, concurring/",
			"/^justice ([a-z']+)\, with .* concurring( in the judgment)*/"
			);
			
		foreach($concurrenceExpressionsArray as $concurrenceExpression){
			if(preg_match($concurrenceExpression,ltrim($paragraphLowerCase),$matchedNames)){
				foreach($matchedNames as $lastName){
					if(!preg_match("/JUSTICE|justice/",$lastName)){
						$lastNameArray[] = $lastName;  
					}
				}	
				$concurrenceCount = $concurrenceCount +1;
			}
		}
		
		/**
		* When no concurrence or dissent is counted, the paragraph
		* belongs to the opinion
		*/
		
		if($concurrenceCount == 0&&$dissentCount == 0) {
			$OpinionOfCourt_Notes = $OpinionOfCourt_Notes . opinionFootnotesFormat($paragraph,$arr);	
			$OpinionOfCourt = $OpinionOfCourt ."\n\n". trim(opinionParagraphRefs($paragraph));		
		}
		
		/**
		* add each paragraph to the proper concurrence
		*/
		switch($concurrenceCount){
			case 1:
				$ConNotes = $ConNotes . opinionFootnotesFormat($paragraph,$arr);
				$Con = $Con ."\n\n". trim(opinionParagraphRefs($paragraph));
				$ConAuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[0])]);
				break;
			case 2:
				$Con2Notes = $Con2Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con2 = $Con2 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con2AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[1])]);
				break;
			case 3:
				$Con3Notes = $Con3Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con3 = $Con3 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con3AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[2])]);
				break;
			case 4:
				$Con4Notes = $Con4Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con4 = $Con4 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con4AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[3])]);
				break;
			case 5:
				$Con5Notes = $Con5Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con5 = $Con5 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con5AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[4])]);
				break;
			case 6:
				$Con6Notes = $Con6Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con6 = $Con6 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con6AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[5])]);
				break;
			case 7:
				$Con7Notes = $Con7Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con7 = $Con7 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con7AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[6])]);
				break;
			case 8:
				$Con8Notes = $Con8Notes . opinionFootnotesFormat($paragraph,$arr);
				$Con8 = $Con8 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Con8AuthorFullName = multiAuthorName($year,$authorByName[strtolower($lastNameArray[7])]);
				break;


		}
		
		/**
		* Count each dissent by matching the start of each line against
		* an expression in $dissentExpressionArray
		*
		* there are only four possible dissents.
		*/
		
		$dissentExpressionsArray = array(
			"/^mr\. justice ([a-z]+) dissenting/",
			"/^mr\. justice ([a-z]+), with .* dissenting/",
			"/^justice ([a-z']+)\, dissenting\./",
			"/^justice ([a-z']+),* with .* dissenting\./",
			"/^chief justice ([a-z']+)[,]* with .* dissenting\./",
			"/^justice ([a-z]+)\, joined .* dissenting\.$/"
			);
			
		foreach($dissentExpressionsArray as $dissentExpression){
			if(preg_match($dissentExpression,ltrim($paragraphLowerCase),$match)){
				foreach($match as $E){
					if(!preg_match("/JUSTICE|justice/",$E)){
						$lastNamed[] = $E; 
					}
				}	
				$dissentCount = $dissentCount +1;	
				$concurrenceCount = 0;
			}
		}
		
		switch($dissentCount){
			case 1:
				$DisNotes = $DisNotes . opinionFootnotesFormat($paragraph,$arr);
				$Dis = $Dis ."\n\n". trim(opinionParagraphRefs($paragraph));
				$DissentAuthor = multiAuthorName($year,$authorByName[strtolower($lastNamed[0])]);
				break;
			case 2:
				$Dis2Notes = $Dis2Notes . opinionFootnotesFormat($paragraph,$arr);
				$Dis2 = $Dis2 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Dissent2Author = multiAuthorName($year,$authorByName[strtolower($lastNamed[1])]);
				break;
			case 3:
				$Dis3Notes = $Dis3Notes . opinionFootnotesFormat($paragraph,$arr);
				$Dis3 = $Dis3 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Dissent3Author = multiAuthorName($year,$authorByName[strtolower($lastNamed[2])]);
				break;
			case 4:
				$Dis4Notes = $Dis4Notes . opinionFootnotesFormat($paragraph,$arr);
				$Dis4 = $Dis4 ."\n\n". trim(opinionParagraphRefs($paragraph));
				$Dissent4Author = multiAuthorName($year,$authorByName[strtolower($lastNamed[3])]);
				break;
		}
	}
	
	$OpName = $name[0];
	
	/**
	* wpiwl: Wikipedia Interwiki Link
	*/
	
	$TemplateUSSCcaseValues["wpiwl"] = wikiCheckPageExistence($OpName, "wikipedia");	
	
	/**
	* attempt to determine the author of each opinion
	*/
	
	$lowersyll = strtolower($Syllabus);
	
	/**
	* use $caseAuthorExpressionArray/$dissentAuthorExpressionArray/
	* $concurrenceAuthorExpressionArray to match an author in the
	* syllabus
	*/
	
	$caseAuthorExpressionArray = array(
		"/[Jj]ustice ([a-zA-Z]+) delivered/",
		"/([A-Z']+), J., delivered the opinion/"
	);
	
	foreach($caseAuthorExpressionArray as $caseAuthorExpression){
		preg_match($caseAuthorExpression,$Syllabus,$match);
		if(isset($match[1])) {
			$CaseAuthorFullName = multiAuthorName($year,$authorByName[strtolower($match[1])]);
		}
	}
	
	if(!isset($CaseAuthorFullName)) { $CaseAuthorFullName = ""; }
	$dissentAuthorExpressionArray = array(
		"/([a-z']+), j., filed a dissenting/",
		"/([a-z]+), c.j., filed a dissenting/"
	);
	
	foreach($dissentAuthorExpressionArray as $dissentAuthorExpression){
		preg_match_all($dissentAuthorExpression,$lowersyll,$authorArray);
		foreach($authorArray[1] as $author){
			$dissentAuthor[] = ucfirst(strtolower(trim($author)));
			$TemplateUSSCcaseValues["dissentAuthorLastName"][] = ucfirst(strtolower(trim($author)));
		}
	}
	
	$concurrenceAuthorExpressionArray = array(
		"/([A-Za-z']+), J., filed a concurring/",
		"/([A-Za-z']+), J., filed an opinion concurring/"
	);
	
	foreach($concurrenceAuthorExpressionArray as $concurrenceAuthorExpression){
		preg_match_all($concurrenceAuthorExpression,$lowersyll,$authorArray);
		foreach($authorArray[1] as $author){
			$concurrenceAuthor[] = ucfirst(strtolower(trim($author)));
			$TemplateUSSCcaseValues["concurrenceAuthorLastName"][] = ucfirst(strtolower(trim($author)));
		}
	}
		
	if(preg_match("/per curiam/",strtolower($syll))&&!isset($CaseAuthorFullName)){
		$perCuriam = "yes";
		$TemplateUSSCcaseValues["perCuriam"] = "yes";
	}else{
		$perCuriam = "";
		$TemplateUSSCcaseValues["perCuriam"] = "";
	}
	
	/**
	* if we found an opinion but couldn't determine the author from the syllabus,
	* determine the author from the first line of the opinion.
	*/
	if($Dis != ""&&!isset($dissentAuthor[0])){
		$missingDissent = 1;
		$dissentAuthor[0] = strstr($DissentAuthor," ");
		$TemplateUSSCcaseValues["dissentAuthorLastName"][0] = $dissentAuthor[0];
	}elseif($Dis == ""){
		$TemplateUSSCcaseValues["dissentAuthorLastName"][0] = "";
	}
	if($Dis2 != ""&&!isset($dissentAuthor[1])){
		$missingDissent = 1;
		$dissentAuthor[1] = strstr($Dissent2Author," ");
		$TemplateUSSCcaseValues["dissentAuthorLastName"][1] = $dissentAuthor[1];
	}elseif($Dis2 == ""){
		$TemplateUSSCcaseValues["dissentAuthorLastName"][1] = "";
	}
	if($Dis3 != ""&&!isset($dissentAuthor[2])){
		$missingDissent = 1;
		$dissentAuthor[2] = strstr($Dissent3Author," ");
		$TemplateUSSCcaseValues["dissentAuthorLastName"][2] = $dissentAuthor[2];
	}elseif($Dis3 == ""){
		$TemplateUSSCcaseValues["dissentAuthorLastName"][2] = "";
	}
	if($Dis4 != ""&&!isset($dissentAuthor[3])){
		$missingDissent = 1;
		$dissentAuthor[3] = strstr($Dis4Author," ");
		$TemplateUSSCcaseValues["dissentAuthorLastName"][3] = $dissentAuthor[3];
	}elseif($Dis4 == ""){
		$TemplateUSSCcaseValues["dissentAuthorLastName"][3] = "";
	}
	if($Con != ""&&!isset($concurrenceAuthor[0])){
		$concurrenceAuthor[0] = ucfirst(strtolower($lastNameArray[0]));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][0] = $concurrenceAuthor[0];
	}elseif($Con == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][0] = "";
	}
	if($Con2 != ""&&!isset($concurrenceAuthor[1])){
		$concurrenceAuthor[1] = ucfirst(strtolower($lastNameArray[1]));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][1] = $concurrenceAuthor[1];
	}elseif($Con2 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][1] = "";
	}
	if($Con3 != ""&&!isset($concurrenceAuthor[2])){
		$concurrenceAuthor[2] = ucfirst(strstr($Con3Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][2] = $concurrenceAuthor[2];
	}elseif($Con3 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][2] = "";
	}
	if($Con4 != ""&&!isset($concurrenceAuthor[3])){
		$missingConcurrence = 1;
		$concurrenceAuthor[3] = ucfirst(strstr($Con4Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][3] = $concurrenceAuthor[3];
	}elseif($Con4 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][3] = "";
	}
	if($Con5 != ""&&!isset($concurrenceAuthor[4])){
		$missingConcurrence = 1;
		$concurrenceAuthor[4] = ucfirst(strstr($Con5Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][4] = $concurrenceAuthor[4];
	}elseif($Con5 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][4] = "";
	}
	if($Con6 != ""&&!isset($concurrenceAuthor[5])){
		$missingConcurrence = 1;
		$concurrenceAuthor[5] = ucfirst(strstr($Con6Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][5] = $concurrenceAuthor[5];
	}elseif($Con6 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][5] = "";
	}
	if($Con7 != ""&&!isset($concurrenceAuthor[6])){
		$missingConcurrence = 1;
		$concurrenceAuthor[6] = ucfirst(strstr($Con7Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][6] = $concurrenceAuthor[6];
	}elseif($Con7 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][6] = "";
	}
	if($Con8 != ""&&!isset($concurrenceAuthor[7])){
		$missingConcurrence = 1;
		$concurrenceAuthor[7] = ucfirst(strstr($Con8Author," "));
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][7] = $concurrenceAuthor[7];
	}elseif($Con8 == ""){
		$TemplateUSSCcaseValues["concurrenceAuthorLastName"][7] = "";
	}
	
	/**
	* clean up openjurist cites
	*/
	$decdate = str_replace("Decided ","",$decdate);
	$decdate = str_replace(".","",$decdate);
	$docket = str_replace("No. ","",$docket);
	$docket = str_replace(".","",$docket);
	$court = str_replace(".","",$court);
	 
	  
	/**
	* let's see if there is a cite in the title
	*/
	$cite = strstr($pg_title,",");
	if(isset($s)){
		$s = explode(" ",$cite);
		if ($s[1]!=""){
			$ctvol = $s[1];
			$ctrep = $s[2];
			$ctpg = $s[3];
		}
	}
	 
	/**
	* what is missing from the metadata?
	*/
	if(!isset($court)){$missing=$missing . "<li>court</li>";}
	if(!isset($arrCite["Volume"])){$missing=$missing . "<li>volume</li>";}
	if(!isset($arrCite["Reporter"])){$missing=$missing . "<li>reporter</li>";}
	if(!isset($arrCite["Page"])){$missing=$missing . "<li>page</li>";}
	if(!isset($name)){$missing=$missing . "<li>case name</li>";}
	if(!isset($courtbelow)){$missing=$missing . "<li>lower court</li>";}
	if(!isset($argdate)){$missing=$missing . "<li>date argued</li>";}
	if(!isset($decdate)){$missing=$missing . "<li>date decided</li>";}
	if(!isset($docket)){$missing=$missing . "<li>docket</li>";}
	if(!isset($CaseAuthorFullName)&&$perCuriam==false){$missing=$missing . "<li>author of the opinion of the court</li>";}
	// if(!isset($arr)){$missing=$missing . "<li>no footnotes?</li>";}
	if(isset($idhunter)){$missing=$missing . "<li>Identified some id. links. Note: This can only figure out links referring to citations in the same paragraph.</li>";}
	if(!isset($concurrenceCount)){$missing=$missing . "<li>No dissent/concurrence?</li>";}
	if($dissent == 1) {$notices = $notices."<li>The syllabus mentions a dissenting opinion</li>";}
	if($perCuriam == 1) {$notices = $notices."<li>The opinion of the court was decided per curiam</li>";}
	if($missingDissent == true) {$notices = $notices."<li>Unknown Dissent Author; is there a dissent mentioned in the syllabus?</li>";}
	if($missingConcurrence == true) {$notices = $notices."<li>Unknown Dissent Author; is there a concurrence mentioned in the syllabus?</li>";}
	 
	$name = str_replace("/>","",$name);
	$name = str_replace("v."," v. ",$name);
	
	$arrCite['Volume'] = str_replace("[[","",$arrCite['Volume']);
	$arrCite['Page'] = str_replace("]]","",$arrCite['Page']);
	
	$articlesList[] = $OpName;
	$articlesList[] = "Talk:".$OpName;
	$articlesList[] = $OpName."/Opinion of the Court";
	$articlesList[] = "Talk:".$OpName."/Opinion of the Court";
	
	if(isset($casecite) && !preg_match("/U\.S\./", $casecite, $m)){
		$ParallelCites[0] = "{{Parallel reporter|".$casecite."}}\n";
	}
	if(isset($casecite2) && !preg_match("/U\.S\./", $casecite2, $m)){
		$ParallelCites[0] = "{{Parallel reporter|".$casecite2."}}\n";
	}
	if(isset($casecite3) && !preg_match("/U\.S\./", $casecite3, $m)){
		$ParallelCites[1] = "{{Parallel reporter|".$casecite3."}}\n";
	}
	
	/**
	* we will say the case author is the author the syllabus
	*/
	$TemplateHeaderValues["AuthorFullName"] = $CaseAuthorFullName;
	
	/**
	* none of the values in $TemplateCaseCaptionValues will change between the opinion, concurrence
	* or dissent
	*/
	$TemplateCaseCaptionValues["court"] = $court;
	$TemplateCaseCaptionValues["arrCite"] = $arrCite;
	$TemplateCaseCaptionValues["name"] = $name;
	$TemplateCaseCaptionValues["courtbelow"] = $courtbelow;
	$TemplateCaseCaptionValues["argdate"] = $argdate;
	$TemplateCaseCaptionValues["decdate"] = $decdate;
	$TemplateCaseCaptionValues["docket"] = str_replace("\n", "", opinionFootnotesFormat($docket, $arr));
	
	$SyllabusBody["mainText"] = $Syllabus;
	$SyllabusBody["notes"] = $SyllabusNotes;
	$SyllabusBody["Cats"] = $Cats;
	
	$fullText[] = buildPage("Syllabus",$name, null, $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $SyllabusBody, $ParallelCites);
	$fullText[] = buildTalkPage("Syllabus", $name, null, $contributor, $decdate, $arrCite);
	
	$CourtOpinionBody["mainText"] = $OpinionOfCourt;
	$CourtOpinionBody["notes"] = $OpinionOfCourt_Notes;
	
	$fullText[] = buildPage("Opinion of the Court",$name, null, $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $CourtOpinionBody, $ParallelCites);
	$fullText[] = buildTalkPage("Opinion of the Court", $name, null, $contributor, $decdate, $arrCite);
	
	if($Con != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[0];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[0];
		
		$TemplateHeaderValues["AuthorFullName"] = $ConAuthorFullName;
		
		$ConBody["mainText"] = $Con;
		$ConBody["notes"] = $ConNotes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[0], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $ConBody, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[0], $contributor, $decdate, $arrCite);
	
	}
	if($Con2 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[1];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[1];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con2AuthorFullName;
		
		$Con2Body["mainText"] = $Con2;
		$Con2Body["notes"] = $Con2Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[1], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con2Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[1], $contributor, $decdate, $arrCite);
	} 
	if($Con3 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[2];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[2];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con3AuthorFullName;
		
		$Con3Body["mainText"] = $Con3;
		$Con3Body["notes"] = $Con3Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[2], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con3Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[2], $contributor, $decdate, $arrCite);
	
	} 
	if($Con4 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[3];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[3];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con4AuthorFullName;
		
		$Con4Body["mainText"] = $Con4;
		$Con4Body["notes"] = $Con4Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[3], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con4Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[3], $contributor, $decdate, $arrCite);
	
	} 
	if($Con5 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[4];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[4];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con5AuthorFullName;
		
		$Con5Body["mainText"] = $Con5;
		$Con5Body["notes"] = $Con5Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[4], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con35Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[4], $contributor, $decdate, $arrCite);

	}
	if($Con6 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[5];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[5];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con6AuthorFullName;
		
		$Con6Body["mainText"] = $Con6;
		$Con6Body["notes"] = $Con6Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[5], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con6Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[5], $contributor, $decdate, $arrCite);
		
	}
	if($Con7 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[6];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[6];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con7AuthorFullName;
		
		$Con7Body["mainText"] = $Con7;
		$Con7Body["notes"] = $Con7Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[6], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con7Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[6], $contributor, $decdate, $arrCite);
	
	}
	if($Con8 != false) {
		$articlesList[] = $name[0]."/Concurrence ".$concurrenceAuthor[7];
		$articlesList[] = "Talk:".$name[0]."/Concurrence ".$concurrenceAuthor[7];
		
		$TemplateHeaderValues["AuthorFullName"] = $Con8AuthorFullName;
		
		$Con8Body["mainText"] = $Con8;
		$Con8Body["notes"] = $Con8Notes;
		
		$fullText[] = buildPage("Concurrence",$name, $concurrenceAuthor[7], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Con8Body, $ParallelCites);
		$fullText[] = buildTalkPage("Concurrence", $name, $concurrenceAuthor[7], $contributor, $decdate, $arrCite);
		}
	if($Dis != false) {
		$articlesList[] = $name[0]."/Dissent ".$dissentAuthor[0];
		$articlesList[] = "Talk:".$name[0]."/Dissent ".$dissentAuthor[0];
		
		$DisBody["mainText"] = $Dis;
		$DisBody["notes"] = $DisNotes;
		
		$fullText[] = buildPage("Dissent",$name, $dissentAuthor[0], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $DisBody, $ParallelCites);
		$fullText[] = buildTalkPage("Dissent", $name, $dissentAuthor[0], $contributor, $decdate, $arrCite);
	}
	if($Dis2 != false) {
		$articlesList[] = $name[0]."/Dissent ".$dissentAuthor[1];
		$articlesList[] = "Talk:".$name[0]."/Dissent ".$dissentAuthor[1];
		
		$Dis2Body["mainText"] = $Dis2;
		$Dis2Body["notes"] = $Dis2Notes;
		
		$fullText[] = buildPage("Dissent",$name, $dissentAuthor[1], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Dis2Body, $ParallelCites);
		$fullText[] = buildTalkPage("Dissent", $name, $dissentAuthor[1], $contributor, $decdate, $arrCite);
	}
	if($Dis3 != false) {
		$articlesList[] = $name[0]."/Dissent ".$dissentAuthor[2];
		$articlesList[] = "Talk:".$name[0]."/Dissent ".$dissentAuthor[2];
		
		$Dis3Body["mainText"] = $Dis3;
		$Dis3Body["notes"] = $Dis3Notes;
		
		$fullText[] = buildPage("Dissent",$name, $dissentAuthor[2], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Dis3Body, $ParallelCites);
		$fullText[] = buildTalkPage("Dissent", $name, $dissentAuthor[2], $contributor, $decdate, $arrCite);
	
	}
	if($Dis4 != false) {
		$articlesList[] = $name[0]."/Dissent ".$dissentAuthor[3];
		$articlesList[] = "Talk:".$name[0]."/Dissent ".$dissentAuthor[3];
		
		$Dis4Body["mainText"] = $Dis4;
		$Dis4Body["notes"] = $Dis4Notes;
		
		$fullText[] = buildPage("Dissent",$name, $dissentAuthor[3], $TemplateHeaderValues, $TemplateUSSCcaseValues, $TemplateCaseCaptionValues, $Dis2Body, $ParallelCites);
		$fullText[] = buildTalkPage("Dissent", $name, $dissentAuthor[3], $contributor, $decdate, $arrCite);
	}
	
	/**
	* run through wikifyCite
	*/
	$fullStr= implode("******",$fullText);
	$fullArray = wikifyCite($fullStr, 0);
	$fullArray = str_replace("U.S.***","U.S.",$fullArray); // so it is not mistaken as a citation
	$name[0] = str_replace("U.S.***","U.S.",$name[0]);
	$fullText = explode("******",$fullArray[0]);
	
	foreach($articlesList as $k=>$c){
		$articlesList[$k] = str_replace("U.S.***","U.S.",$c);
	}
	
	$articlesList[] = $arrCite['Volume']." ".$arrCite['Reporter']." ".$arrCite['Page'];
	$fullText[] = "{{-start-}}
	'''".$arrCite['Volume']." ".$arrCite['Reporter']." ".$arrCite['Page']."'''
	#REDIRECT [[".$name[0]."]]
	 
	{{-stop-}}";
	
	/**
	* if there are additional cites to this case, make redirects
	*/
	if(isset($casecite) && !preg_match("/U\.S\./", $casecite, $m)){
		$articlesList[] = $casecite;
		$fullText[] = "{{-start-}}
		'''".$casecite."'''
		#REDIRECT [[".$name[0]."]]
		 
		{{-stop-}}";
		}
	if(isset($casecite2) && !preg_match("/U\.S\./", $casecite2, $m)){
		$articlesList[] = $casecite2;
		$fullText[] = "{{-start-}}
		'''".$casecite2."'''
		#REDIRECT [[".$name[0]."]]
		 
		{{-stop-}}";
		}
	if(isset($casecite3) && !preg_match("/U\.S\./", $casecite3, $m)){
		$articlesList[] = $casecite3;
		$fullText[] = "{{-start-}}
		'''".$casecite3."'''
		#REDIRECT [[".$name[0]."]]
		 
		{{-stop-}}";
	}
	
	$fullText = str_replace("  v.  "," v. ",$fullText);

return array($fullText, $articlesList);
}
?>
<html>
<head>
<script src="jquery.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script>
$(document).ready(function() {
	$('.saveas').show();
	$('.saveto').change(function() {
		if($('.saveto').val() != 'new') {
			$('.saveas').hide();
		} 
		else {
			$('.saveas').show();
		}
			
	});

});

</script>
<style>
fieldset {
	width:70em;
}
</style>
<title>Format a U.S. Supreme Court case</title>
</head>
<fieldset>
<legend>Enter URL of HTML file to format</legend>
<form method="get">
<label for="u">URL</label>
<input type="text" name="u" value="<?php if(isset($_GET['u'])) { print $_GET['u']; } ?>"><br />
<button type="submit">Submit</button><br />
</form></fieldset>

<?php
/**
* URL specified by batch.php?us=[url]
*
* Multiple URLS can be specified by seperating with a |
*/

if(isset($_GET["u"])) {
	$URLs = explode("|",$_GET["u"]);
	print "<fieldset> \n<legend>Create bot file</legend> \n<ul>";


	foreach($URLs as $URL){
		if($URL != ""){
			print "<li>".$URL."</li>";
		}
	}
	
	print "</ul> \n";

	print "<textarea name=\"result\" cols=100 rows=30>";
	
	foreach($URLs as $URL){
	
		$pagefile = batchWikify($URL);
		if(is_array($pagefile)){
			print(trimLine(implode("\n",$pagefile[0])));
			
			/**
			* report of pages generated
			*/
			print "\n{{-start_report-}} \n==Articles generated== \n";
			foreach($pagefile[1] as $list){
				print "*".$list."\n";
			}
			print "\n{{-stop_report-}}";
		
		}
	}
	print "</textarea> \n</fieldset>";
}
?>
<ul>
<?php if(isset($dupList)) { foreach($dupList as $dupItem) { print "<li>".$dupItem."</li>"; } } ?>
</ul>
</html>
