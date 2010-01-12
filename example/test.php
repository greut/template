#!/usr/bin/env php
<?php

include dirname(__FILE__)."/../src/template.php";

$template = new Template(dirname(__FILE__)."/tpl",
                         array("lang" => "en",
                               "charset" => "utf-8"));

$body = $template->index(array("title" => "test"));
$template->applyHeaders();
echo $body;
