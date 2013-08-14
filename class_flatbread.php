<?
	

	class flatbread {
		function __construct($customSettings=array()) { 
			$this->settings = array(
				"path"    => "./", // Path to cache files
				"expire"  => 10, // Default expiration for cached files, in minutes
				"suffix"  => ".cache", // Extension added to cache files
				"method"  => "echo", // Method or handling. echo | return
				"hashkey" => true, // Hash keys with md5
				"wrapper" => true // Wrap cached content with HTML comments
			);

			$this->settings     = array_merge($this->settings, $customSettings);
			$this->fromcache    = "";
			$this->wrapperOpen  = "\n<!-- START cache -->\n";
			$this->wrapperClose = "\n<!-- END cache -->\n";
			$this->cacheIndex   = $this->settings["path"]."cache.index";

			$this->createIndex();
		}



		// Main condition for cache checking.
		// Returns TRUE if content is expired or not found
		// If content cache is found, it will either echo the content or return string (depending on settings)
		//
		// Syntax: 	expire(str $slug);
		// 
		// $slug	Key used for content. Should be unique (ie, ID number of hash)

		function expired($slug) {
			if (!$time) { $time = $this->settings["expire"]; }

			$path = $this->createPath($slug);
			$time = $this->getIndex($slug);

			if ($this->cacheExists($path) < $time) {
				$status = false;

				switch ($this->settings["method"]) {
					
					default:
					case "return":
						$this->fromcache = $this->wrapper(file_get_contents($path));
						break;

					case "echo":
						echo $this->wrapper(file_get_contents($path));
						break;


					case "include":
						include($path);
						break;
				}

				return($status);

			} else {
				return(true);
			}
		}


		// Returns any cached content that matches key. Returns empty if none.
		// Use this when you want to do something with the content other than output
		function get($slug) {

			$s = $this->settings["method"];
			$this->settings["method"] = "return";
			$this->expired($slug);
			$this->settings["method"] = $s;
		
			return($this->fromcache);
		}


		// Saves new content to cache
		// Syntax: save(str $slug, str $content, int $expire);
		//
		// $slug		Key used for content. Should be unique (ie, ID number of hash)
		// $content		Content you want cached (html, etc)
		// $expire		Number of minutes until cache expires
		function save($slug, $content, $expire) {
			$path = $this->createPath($slug, $expire);

			$fr = fopen($path, "w");
			fwrite($fr, $content);
			fclose($fr);

			$this->updateIndex($slug, $expire);

			return($path);
		}

		// Deletes all cache files and resets the index
		function clear() {
			if ($handle = opendir($this->settings["path"])) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." and $entry != "..") {
			        	@unlink($this->settings["path"].$entry);
			        }
			    }

			    closedir($handle);
			}

			$this->createIndex(true);
		}




		function getkey($slug) {
			if ($this->settings["hashkey"]) {
				$slug = md5($slug);
			}

			return($slug);
		}


		// Creates read/write path for cache files
		function createPath($slug) {
			$path = $this->settings["path"].$this->getkey($slug).$this->settings["suffix"];
			return($path);
		}


		// Creates cache index
		function createIndex($reset=false) {
			if (!file_exists($this->cacheIndex) or $reset) {
				$new = array();

				$fr = fopen($this->cacheIndex, "w");
				fwrite($fr, serialize($new));
				fclose($fr);
			}
		}

		// Updates cache index
		function updateIndex($slug, $expire) {
			$a_index = $this->getIndex();

			$a_index[$this->getkey($slug)] = $expire;

			$fr = fopen($this->cacheIndex, "w");
			fwrite($fr, serialize($a_index));
			fclose($fr);
		}

		// Unpacks cache index or does lookup
		function getIndex($key=false) {
			$flat    = file_get_contents($this->cacheIndex);
			$a_index = unserialize($flat);


			if ($key) {
				$key     = $this->getkey($key);
				$a_index = $a_index[$key];
			}

			return($a_index);
		}


		// Checks to see if cache file exists. Returns minutes since modified.
		function cacheExists($path) {
			if (file_exists($path)) {
				$mins = $this->getCacheTime($path);
				return($mins);
			} else {
				return(999999999);
			}
		}

		// Calculates difference in time
		function getCacheTime($path) {
			$filet = date("U", filemtime($path));
			$now   = time();
			$mins  = round(($now - $filet) / 60);

			return($mins);
		}


		// Debug wrapper for output
		function wrapper($content) {
			if ($this->settings["wrapper"]) {
				return($this->wrapperOpen.$content.$this->wrapperClose);
			} else {
				return($content);
			}
		}

	}

?>