<?php
$this->httpHeader("Content-type: text/html; charset=".$charset);
?>
<!DOCTYPE html>
<html lang=<?php echo $lang ?>>
    <meta charset=<?php echo $charset ?>>
    <title><?php echo isset($title) ? $title : "Untitled" ?></title>
<?php $this->block("style") ?>
<style>
body {background:#f00}
</style>
<?php $this->endblock() ?>
</head>
<body>
<div id=doc>
<?php $this->block("doc") ?>
        <header>
<?php $this->block("header"); $this->endblock() ?>
        </header>
        <article>
<?php $this->block("article"); $this->endblock() ?>
        </article>
        <footer>
<?php $this->block("footer"); $this->endblock() ?>
        </footer>
<?php $this->endblock() ?>
</div>
<?php $this->block("script")?>
<script>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'test']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
})();
</script>
<?php $this->endblock() ?>
</body>
</html>
