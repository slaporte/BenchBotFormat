<?php

// General variables

// textinfo values
$contributor		= '[[User:BenchBot|BenchBot]]';
$reporter			= 'U.S.';


// This sets the xpath for variables used in the script

// These fill the {CaseCaption}
$xpathCourt		= '//p[@class="court"]';
$xpathTitle 		= '//title';
$xpathName 		= '//p[@class="parties"]';
$xpathAuthor 		= '//meta[@name="AUTHOR"]/@content';
$xpathCasecite 	= '//p[@class="case_cite"]';
$xpathCite2	 	= '//p[@class="case_cite"]';
$xpathCite3		= '//p[@class="case_cite"]';
$xpathCourtbelow 	= '//meta[@name="COURTBELOW"]/@content';
$xpathDate1 		= '//p[@class="date"]';
$xpathDate2 		= '//p[@class="date"]';
$xpathDocket 		= '//p[@class="docket"]';
$xpathParty1 		= '//meta[@name="PARTY1"]/@content';
$xpathParty2 		= '//meta[@name="PARTY2"]/@content';

// Odds and ends
$xpathFoots 		= '//div[@class="footnote"]';
$xpathParagraph	= '//div[@class="num"]';
$xpathSyllabus		= '//div[@class="prelims"]';

?>