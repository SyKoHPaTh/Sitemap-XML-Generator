<?php
/**
 * SitemapXMLGenerator Class
 *
 * @author Eric Casequin & Alex Beck
 */
class SitemapXMLGenerator 
{
  
  public $content  = "";
  public $links    = array();


  function __construct($settings)
  {

    $this->settings = $settings;
    $this->init();
    
  }
  
  
  private function init()
  {
    $this->content = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n<!--  created with SyKoHPaTh's SiteMap.XML generator  www.sykohpath.com  -->\n";
  }
  
  
  /**
   * Create the contents of the xml
   *
   * @return void
   */
  public function create()
  {
    // if ($this->settings["website_url"] == "") 
    // {
    //   die("Must supply a website_url");
    // }
    
    $websites = $this->settings["websites"];

    $x = 0;

  	while(1==1){
  		//gen list
  		print "Scanning [$x of " . (count($websites)-1) . "]: " . $websites[$x] . "<br>\n";
  		$linkmatch = digger($websites[$x]);
  		//scan list
  		foreach($linkmatch as $key=>$value){
  			//print $key . ": " . $value . "\n";
  			//filter bad data


  				//check link against $websites[0]
  			if(substr($value, 0, strlen($websites[0])) == $websites[0]){
  				if(!(in_array($value, $websites))){
  					//push to array
  					$websites[] = $value;
  					$xmloutput .= "<url>\n\t<loc>" . $value . "</loc>\n</url>\n";
  				}
  			} else {
  				//check if it's a foreign link
  				if(!substr($value, 0, 4) == "http"){


  					//add scanned websites to front, and see if it's a valid link
  					//cut out everything after the slash:  http://w3dev.millerind.com/parts/index.php?bid=2
  					$pattern = preg_replace("/[^\/]*$/s", "", $websites[$x]);

  					$value = trim($value); //strip whitespace BAD CODER, BAD!
  					$value = preg_replace("/^[\/]/s", "", $value); //strip beginning / if there is one

  					if(checklink($pattern . $value)){
  						$value = $pattern . $value;
  						if(substr($value, 0, strlen($websites[0])) == $websites[0]){
  							if(!(in_array($value, $websites))){
  								//push to array
  								$websites[] = $value;
  								$xmloutput .= "\t<url>\n\t\t<loc>" . $value . "</loc>\n</url>\n";
  							}
  						}					
  					}
  				}
  			}

  		}

  		//echo "Total links: " . count($websites) . "<br>\n";
  		//if nothing new was added, exit loop
  		if($x + 1 >= count($websites)){ break; }
  		//if limit reached, exit loop
  		if($x > $sitemap_limit - 1){ break; }
  		$x=$x+1;
  	}
    
  }
  
  /**
   * Output content
   *
   * @return $string
   */
  public function output()
  {
    return $this->content;
  }
  
  
  /**
   * Dig the contents of the supplied url and strip links into an array
   *
   * @param string $scanlink 
   * @return array
   */
  function dig($scanlink) {
		
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


  /**
   * Evaluate validity of a link
   *
   * @param string $linkcheck 
   * @return bool
   */
	function checklink($linkcheck) {
		$linkcontents = @file_get_contents($linkcheck);
		if(!$linkcontents) {
			print "Unable to open: {$linkcheck}\n";
			return false;
		}
		return true;
	}
	
  
  
}
