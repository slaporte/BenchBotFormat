<?php
$formats = array(
	"([0-9]+) (U.S.) ([0-9]+)", 		// U.S. Reports						U.S. Supreme Court	(1975-present)
	"([0-9]+) (U.S. \(1 Wall.\)) ([0-9]+)", 		// Wallace (1863-1874)
	"([0-9]+) (U.S. \(1 Black\)) ([0-9]+)", 		// Black (1861-1862)
	"([0-9]+) (U.S. \(1 How.\)) ([0-9]+)", 			// Howard (1843-1860)
	"([0-9]+) (U.S. \(1 Pet.\)) ([0-9]+)", 			// Peters (1828-1842)
	"([0-9]+) (U.S. \(1 Wheat.\)) ([0-9]+)", 		// Wheaton (1816-1827)
	"([0-9]+) (U.S. \(1 Cranch\)) ([0-9]+)", 		// Cranch (1801-1815)
	"([0-9]+) (U.S. \(1 Dallas\)) ([0-9]+)", 		// Dallas (1790-1800)
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
?>