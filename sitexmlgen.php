<?php
/* ---- Information ----

	Name: sitexmlgen.php  "sitemap.xml Generator"
	Last Updated:  20110512 080000
	Page Version */ $pagever = "sitexmlgen.php v1.0"; /*
	Author: SyKoHPaTh

 ---- Version History ----

 	1.0		Initial Coding
 
 -------------------------
  PURPOSE:
  	"crawl" a site and record the links if they are valid.
  	Note: this only picks up links between <a> tags.
	
 -------------------------
  TODO:
 
 -------------------------
  LICENSE:

  	Modification: OK, but must keep credit line: "SyKoHPaTh (www.sykohpath.com)", and this License.  Any modifications MUST be written in "Version History", with your name and/or handle, and what the modification was.
  	Free for public and commercial use.  If you paid for this, you got scammed.

--------------------------
*/


/* -------- VARIABLES -------- */

	$linklist = array();

	$linklist[0] = "http://www.sykohpath.com/";

	$sitemap_limit = 50000;  //enforced by sitemap.org, max number of <url> links in one sitemap XML file.
								// there is also a 10MB limit to sitemap XML files, but we're not checking for that here.


/* -------- FUNCTIONS -------- */

	function digger($scanlink) {
		//does the work of scanning a page and putting links into an array
		
		$linkcontents = @file_get_contents($scanlink);
		if(!$linkcontents) {
			print "Unable to open: {$linkcheck}\n";
			return array();
		}

		$linkinfo = parse_url($scanlink);
		$linkcore = $linkinfo['scheme'] . "://" . $linkinfo['host'];

		$linkcontents_strip = strip_tags($linkcontents, "<a>");
		$linkcontents_mod = preg_replace("/<a([^>]*)href=\"\//is", "<a$1href=\"{$linkcore}/", $linkcontents_strip);
		$linkcontents_mod = preg_replace("/<a([^>]*)href=\"\?/is", "<a$1href=\"{$scanlink}/?", $linkcontents_mod);
		preg_match_all("/<a(?:[^>]*)href=\"([^\"]*)\"(?:[^>]*)>(?:[^<]*)<\/a>/is", $linkcontents_mod, $matches);

		return $matches[1];
	}

	function checklink($linkcheck) {
		//simply checks a link to see if it loads up or not
		$linkcontents = @file_get_contents($linkcheck);
		if(!$linkcontents) {
			print "Unable to open: {$linkcheck}\n";
			return false;
		}
		return true;
	}



/* -------- Initial header thing -------- */

	$xmloutput = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n<!--  created with SyKoHPaTh's SiteMap.XML generator  www.sykohpath.com  -->\n";

	$x = 0;

	while(1==1){
		//gen list
		print "Scanning [$x of " . (count($linklist)-1) . "]: " . $linklist[$x] . "<br>\n";
		$linkmatch = digger($linklist[$x]);
		//scan list
		foreach($linkmatch as $key=>$value){
			//print $key . ": " . $value . "\n";
			//filter bad data


				//check link against $linklist[0]
			if(substr($value, 0, strlen($linklist[0])) == $linklist[0]){
				if(!(in_array($value, $linklist))){
					//push to array
					$linklist[] = $value;
					$xmloutput .= "<url>\n\t<loc>" . $value . "</loc>\n</url>\n";
				}
			} else {
				//check if it's a foreign link
				if(!substr($value, 0, 4) == "http"){


					//add scanned linklist to front, and see if it's a valid link
					//cut out everything after the slash:  http://w3dev.millerind.com/parts/index.php?bid=2
					$pattern = preg_replace("/[^\/]*$/s", "", $linklist[$x]);

					$value = trim($value); //strip whitespace BAD CODER, BAD!
					$value = preg_replace("/^[\/]/s", "", $value); //strip beginning / if there is one

					if(checklink($pattern . $value)){
						$value = $pattern . $value;
						if(substr($value, 0, strlen($linklist[0])) == $linklist[0]){
							if(!(in_array($value, $linklist))){
								//push to array
								$linklist[] = $value;
								$xmloutput .= "\t<url>\n\t\t<loc>" . $value . "</loc>\n</url>\n";
							}
						}					
					}
				}
			}

		}

		//echo "Total links: " . count($linklist) . "<br>\n";
		//if nothing new was added, exit loop
		if($x + 1 >= count($linklist)){ break; }
		//if limit reached, exit loop
		if($x > $sitemap_limit - 1){ break; }
		$x=$x+1;
	}



	//Optional tags for each link.
	//<lastmod>" . date("Y-m-d") . "</lastmod>\n
	//<changefreq>yearly</changefreq>\n
	//<priority>0.5</priority>\n

	$xmloutput .= "</urlset>";

print "<br>\n<br>\n-----------------------------------------------------------<br>\n           XML IS BELOW (view source)<br>\n           Copy and paste into \"sitemap.xml\"<br>\n-----------------------------------------------------------<br>
\n" . $xmloutput;

?>