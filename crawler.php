<?php
/*
* November, 2014
* Crawler class to do the deep link crawling
* Author - Rishabh Mehan (me@rishabhmehan.com)
*/

class Crawler{
	private $BASE;
	private $list;
	private $seen;

	public function Crawler($BASE){
		//Initializing 
		$this->BASE = $BASE;
		$this->list = array();
		$this->seen = array();
	}

	private function crawl_page($url){

		//Maintain an array with key as the url and mark is it has already been visited.
	    if (isset($this->seen[$url])) { 
	          //if the url is already seen then return empty.
	        return;
	    }
	    //Marking the URL as viewed
	    $this->seen[$url] = true;

	    // Fetching the DOM Object
	    $dom = new DOMDocument('1.0');
	    @$dom->loadHTMLFile($url);
	    $anchors = $dom->getElementsByTagName('a');

	    foreach ($anchors as $element) {
	    	//Getting the href attribute for each 'a' tag
	        $href = $element->getAttribute('href');
	        $href = $this->clean_url($href, $url);
	        
	        // Re-checking for the valid url after URL cleaning
            if(filter_var($href, FILTER_VALIDATE_URL)) {
            	if(substr($href,0,strlen($this->BASE)) == $this->BASE){
	            	//Adding the URL to the list, and making sure there are no duplicates.
	                array_push($this->list, $href);
	                $this->list = array_unique($this->list);
	                //recursively calling the method to find deeper links
	                $this->crawl_page($href);   
                }  
            }   
	    }

	}
	/* clean_url : This method forms the URLs (eg- /xyz will become <url>/xyz)
	 * @param $href : The url fetched href attribute value
	 * @return The formed URL
	 */
	private function clean_url($href, $url){
		//Strip the anchor tags, and the trailing slashes.
		$parts_href = explode('#', $href);
	    $href = $parts_href[0];
		$href = rtrim($href,'/');
		// Validating the URL and checking if it's a part of BASE Url
        if(!filter_var($href, FILTER_VALIDATE_URL) && !(substr($href,0,strlen($this->BASE)) == $this->BASE)){
            if ((0 !== strpos($href, 'https')) || (0 !== strpos($href, 'http'))) {
                $path = '/' . ltrim($href, '/');
                if (extension_loaded('https') || extension_loaded('http')) {
                    $href = http_build_url($url, array('path' => $path));
                } else {
                    $parts = parse_url($url);
                    $href = $parts['scheme'] . '://';
                    if (isset($parts['user']) && isset($parts['pass'])) {
                        $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                    }
                    $href .= $parts['host'];
                    if (isset($parts['port'])) {
                        $href .= ':' . $parts['port'];
                    }                  
                    $href .= $path;
                }
            }
        }

        return $href;
	}

	/*
	 * Returns the JSON of the list of URLs Crawled
	 */
	public function getJSON(){
		return json_encode($this->list);
	}

	/*
	 * Returns the Alphabetically sorted array of the list of URLs Crawled
	 */
	public function getSorted(){
		sort($this->list, SORT_NATURAL | SORT_FLAG_CASE);
	}
	/*
	 * Add additional data to the list of URLs.
	 */
	public function add_extra_data(){
		$this->list['Base_URL'] = $this->BASE;
		$this->list['Total_Links'] = sizeof($this->list)-1;
	}

	/*
	 * Main method to invoke crawling
	 */
	public function start_crawl(){
		// Start the crawling
		$this->crawl_page($this->BASE);
		// Sort the list alphabetically
		$this->getSorted();
		// Add extra data
		$this->add_extra_data();
		// Return the json
		return $this->getJSON();
	}

}