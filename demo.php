<?
	date_default_timezone_set('America/New_York'); // Remove this if you don't need it

	include_once("class_flatbread.php"); // Load the class
	$cache = new flatbread(array("path"=>"./cachefiles/")); // Define cache settings

	if ($_GET["reset"]) { $cache->clear(); } // For demo purposes only
?>
<html>
<head>
	<title>Flatbread Demo</title>
</head>
<body>
	<header>
		<h1>Site header</h1>
		<hr />
	</header>
	<article>
	<?
		// Check to see if content has existing cache file; if not, create and save
		// The key should be unique to the content, probably a record ID in a real case
		if ($cache->expired("article")) {
			$article = "<p>This is article content for the Flatbread demo.</p><p>Flaming atomic pumphandle chicken wing cobra clutch eye poke into a inverted knife-edged Saskatchewan body slam, into a knife-edged front gutwrench body slam into a diving bionic Canadian arm drag, follwed by a spinning sliding bionic catapult DDT into a flaming triple Canadian neck breaker DDT piledriver smash for the three-count.</p>";

			echo $article;

			$cache->save("article", $article, 5); // Saves content to cache with 5 min expiration
		}
	?>
	</article>
	<footer>
		<hr />
		<h3>Site footer</h3>
		<p><small><a href="demo.php?reset=cache">Clear cache</a></small></p>
</body>
</html>