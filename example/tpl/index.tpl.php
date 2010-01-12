<?php
$this->inherits("layout");

$this->block("style");
?>
<link rel=stylesheet href=styles.css type="text/css">
<?php
$this->endblock();
$this->block("doc");
?>
<h1><?php echo $title ?></h1>
<?php
    echo $this->loremipsum();
    echo $this->loremipsum();
$this->endblock();
$this->block("script", "before");
?>
<script>
alert("Hello !");
</script>
<?php
$this->endblock();
?>
