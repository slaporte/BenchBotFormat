8-14-2010
version 1

This tool process United States Supreme Court cases for bot upload.


== Input ==

Specify the input file(s) with ?u= in the URL.  The script is designed to process files from <http://bulk.resource.org/courts.gov/c/>, but it can also process files from <http://openjurist.org/>. 

For example: <format_case.php?u=http://openjurist.org/331/us/96/mccullough-v-kammerer-corporation>

To specify multiple files, separate each url by a vertical bar.

For example: <format_case.php?u=http://openjurist.org/5/us/137/william-marbury-v-james-madison|http://openjurist.org/449/us/383/upjohn-company-v-united-states>


== Output ==

When processed, a case includes:
*The syllabus
*Talk page for the syllabus
*The opinion of the court
*Talk page for the opinion of the court
*Each concurrence
*Talk page for each concurrence
*Each dissent
*Talk page for each dissent
*Redirect from each citation to the syllabus page

Each page begins with {{-start-}} and end with {{-stop-}}, and the first line of each page is the page name surrounded by three single-quotation marks ('''page name''').

A summery of all the generated pages is included between {{-start_report-}} and {{-stop_report-}} after the last page of each case.

The pages can be added to Wikisource using pywikipediabot's pagefromfile.py.  Use the -notitle option to avoid including the page name in each article. More information on pywikipediabot and pagefromfile.py is available at <http://pywikipediabot.sourceforge.net/> and <http://meta.wikimedia.org/wiki/Pywikipediabot/pagefromfile.py>.


== Necessary templates ==

The following templates are expected:
*{{Header}}
*{{USSCcase}}
*{{USSCcase2}}
*{{CaseCaption}}
*{{PD-USGov}}
*{{WikiProject USSC}}
*{{textinfo}}
*{{ref}}
*{{note}}
*{{Parallel reporter}}

More information on templates, formatting, and style for United States Supreme Court cases is available at <http://en.wikisource.org/wiki/Wikisource:WikiProject_U.S._Supreme_Court_cases>.

