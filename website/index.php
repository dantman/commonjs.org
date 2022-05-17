<?php

function starts_with($haystack, $needle) {
	return substr($haystack, 0, strlen($needle)) === $needle;
}

$domain = $_SERVER["HTTP_HOST"] ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
$base = "http://$domain";
$draftsite = $draft = starts_with($domain, "draft.");
$draft = true; // FlaggedRevs is disabled

$clearcache = @$_SERVER["HTTP_PRAGMA"] == "no-cache";

// $memc = new Memcache();
// $memc->addServer('localhost', 11211);

// function cache_get($key) {
// 	global $memc;
// 	return $memc->get($key, MEMCACHE_COMPRESSED);
// }

// function cache_set($key, $content, $expires) {
// 	global $memc;
// 	$memc->set($key, $content, MEMCACHE_COMPRESSED, $expires);
// }

// function cache_delete($key) {
// 	global $memc;
// 	$memc->delete($key);
// }

function cache_get($key) {
	return apc_fetch($key);
}

function cache_set($key, $content, $expires) {
	return apc_store($key, $content, $expires);
}

function cache_delete($key) {
	apc_delete($key);
}

function page_get_stable($title, $encoded=false) {
	global $draft;
	if ( $draft )
		return 0;
	if ( !$encoded )
		$title = urlencode($title);
	$q = unserialize(file_get_contents("http://wiki.commonjs.org/api.php?action=query&prop=flagged&titles={$title}&format=php"));
	$q = $q["query"]["pages"];
	$q = reset($q);
	if ( !@$q["flagged"] )
		return 0;
	$q = $q["flagged"]["stable_revid"];
	return $q;
}

function page_get_contents($title) {
	global $draft, $clearcache;
	$title = urlencode($title);
	/*$q = unserialize(file_get_contents("http://wiki.commonjs.org/api.php?action=query&prop=revisions&titles={$title}&rvprop=content&format=php"));
	$q = $q["query"]["pages"];
	$q = reset($q);
	$q = $q["revisions"];
	$q = reset($q);
	$q = $q["*"];
	return $q;*/
	$key = $draft ? "commonjs.org-rawfromwiki-$title" : "commonjs.org-draft-rawfromwiki-$title";
	if ( $clearcache ) {
		cache_delete($key);
		$content = false;
	} else {
		$content = cache_get($key);
	}
	if ( $content === false ) {
		$stableid = page_get_stable($title, true);
		$url = "http://wiki.commonjs.org/index.php?title={$title}&oldid=$stableid&action=raw";
		header("X-Page-Source: $url", false);
		$content = @file_get_contents($url);
		if ( $content === false )
			$content = "<--404-->";
		cache_set($key, $content, 60 * 60 * 60);
	}
	if ( $content === "<--404-->" )
		return false;
	return $content;
}

function page_get_rendered($title) {
	global $base, $draft, $clearcache;
	$title = urlencode($title);
	$key = $draft ? "commonjs.org-fetchfromwiki-$title" : "commonjs.org-draft-fetchfromwiki-$title";
	if ( $clearcache ) {
		cache_delete($key);
		$content = false;
	} else {
		$content = cache_get($key);
	}
	if ( $content === false ) {
		$stableid = page_get_stable($title, true);
		if ( $draft || $stableid !== 0 ) {
			// Outside of drafts we only accept pages that have been reviewed, if stableid is 0 then pretend it was a 404
			$url = "http://wiki.commonjs.org/index.php?title={$title}&oldid=$stableid&action=render";
			header("X-Page-Source: $url", false);
			$content = @file_get_contents($url); // @todo Perhaps we can replace oldid with stable=1 when we upgrade
		} else {
			$content = false;
		}
		if ( $content === false )
			$content = "<--404-->";
		cache_set($key, $content, 60 * 60 * 60);
	}
	if ( $content === "<--404-->" )
		return false;

	$content = preg_replace('#<div id=\'mw-revisiontag-old\'(?:.+?)>(?:.+?)</div>#', "", $content);
	$content = preg_replace('#<span class="editsection">\[<a(?:.+?)>edit</a>\]</span>\s*#', "", $content);
	$content = preg_replace("#http://wiki.commonjs.org/wiki/Website:Index(/[^\s\"'<>]+)?#", "$base\$1/", $content);
	if ( !$draft )
		$content = preg_replace('#\s+rel="nofollow"#', "", $content);

	return $content;
}

function get_redirects() {
	$redirects = array();
	$redir_page = page_get_contents("Website:Redirects");
	$redir_lines = explode("\n", $redir_page);
	foreach ( $redir_lines as $line ) {
		$m = array();
		if ( preg_match('/^\*\s*(.+?)\s*->\s*(.+?)\s*$/', $line, $m) ) {
			$re = $m[1];
			$re = str_replace("/", "\\/", $re);
			$re = "/^(?:$re)$/";
			$redirects[$re] = $m[2];
		}
	}
	return $redirects;
}

function try_redirect($path) {
	$redirects = get_redirects();
	foreach ( $redirects as $redirect => $to ) {
		$m = array();
		if ( preg_match($redirect, $path, $m) ) {
			$new_path = $to;
			foreach( array_reverse($m, true) as $n => $val )
				$new_path = str_replace("\${$n}", $val, $new_path);
			return $new_path;
		}
	}
	return false;
}

$uri = $_SERVER["REQUEST_URI"];
$p = strpos($uri, "?");
$path = $uri;
if ( $p !== false )
	$path = substr($path, 0, $p);

$redir = try_redirect($path);
if ( $redir ) {
	header("HTTP/1.1 303 See Other");
	header("Location: $base$redir");
	echo "$base$redir";
	exit(1);
}

if ( $path === "/style.css" ) {
	header("Content-Type: text/css; charset=UTF-8");
	header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
	echo page_get_contents("Website:Style.css");
	exit(1);
}

if ( $path === "/robots.txt" ) {
	header("Content-Type: text/plain; charset=UTF-8");
	header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
	if ( $draftsite ) {
		echo file_get_contents("./draft-robots.txt");
	} else {
		echo page_get_contents("Website:Robots.txt");
	}
	exit(1);
}

$wiki_title = rtrim("Website:Index$path", "/");
$page_content = page_get_rendered($wiki_title);

$s404 = false;
if ( $page_content === false ) {
	header("HTTP/1.1 404 Not Found");
	$page_content = page_get_rendered("Website:404");
	$s404 = true;
}

$sidebar_content = page_get_rendered("Website:Sidebar");

header("Content-Type: text/html; charset=UTF-8");
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 30 * 60));
?><!DOCTYPE html>
<html>
<head>
<title>CommonJS: JavaScript Standard Library</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php if ( $draft ) { ?><meta name="robots" content="noindex,nofollow"><?php } ?>
<link rel=edit title="Edit" href="http://wiki.commonjs.org/index.php?title=<?php echo urlencode($wiki_title); ?>&amp;action=edit" />
<link href="/style.css" rel="stylesheet" type="text/css">
</head>
<body class="<?php echo $s404 ? 'status404' : (starts_with($path, '/impl/') ? 'impl' : (starts_with($path, '/specs/') ? 'specs' : '')); ?>">
<div class="header_wrapper">
  <div class="header_bg">
    <div class="nav_wrapper">
      <div class="nav_div"></div>
      <div class="nav_item nav2"><a href="/impl/">get it</a></div>
      <div class="nav_div"></div>
      <div class="nav_item nav1"><a href="/specs/">spec</a></div>
      <div class="nav_div"></div>
    </div>
    <a href="/"><img border="0" class="logo" src="http://wiki.commonjs.org/images/3/3a/Website-Logo.png" alt="CommonJS"></a>
    <div class="tagline">javascript: not just for browsers any more!</div>
  </div>
</div>
<div class="content_wrapper">
  <div class="content_bg">
    <div class="content">
      <div class="content-left">
<?php echo $page_content ?>
      </div>
      <div class="news_wrapper">
        <?php echo $sidebar_content ?>
      </div>
      <div class="col_level">&nbsp;</div>
    </div>
  </div>
</div>
<div class="footer_wrapper">Copyright © 2009 - Kevin Dangoor and many <a href="/contributors/">CommonJS contributors</a>, licensed under a <a href="/license/">MIT license</a>.
Website backend and wiki managed and hosted by <a href="http://danielfriesen.name/">Daniel Friesen</a>.
</div>
<!-- <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-20195316-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script> -->
</body>
</html><?php
if ( isset( $memc ) ) {
	$memc->close();
}
