<?php
/*
Copyright (c) 2010, doSimple, Yoan Blanc <yoan@dosimple.ch>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this
list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution.
Neither the name of the doSimple nor the names of its contributors may be
used to endorse or promote products derived from this software without specific
prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class Template {
    private $paths = array();
    private $headers = array();
    private $inherits = array();
    private $blocks = array();
    private $blocknames = array();
    private $caches = array();
    private $args = array();
    public $globals = array();

    function __construct($base, $globals = null, $ext = ".tpl.php") {
        $this->paths[] = $base."/";
        $this->ext = $ext;

        $this->globals = (array)$globals;
    }

    function httpHeader() {
        $args = func_get_args();
        $this->headers[] = $args;
    }

    function applyHeaders() {
        foreach($this->headers as $header) {
            call_user_func_array("header", $header);
        }
    }

    function inherits($name) {
        $this->inherits[$this->file][] = $name;
    }

    function block($blockname, $mode="replace") {
        $this->blocknames[] = array($blockname, $mode);
        ob_start("mb_output_handler");
    }

    function endblock() {
        list($blockname, $mode) = array_pop($this->blocknames);

        if(!isset($this->blocks[$blockname]) && $mode !== false) {
            $this->blocks[$blockname] = array("content" => ob_get_contents(), "mode" => $mode);
        } else {
            switch($this->blocks[$blockname]["mode"]) {
                case "before":
                case "prepend":
                    $this->blocks[$blockname] = array(
                        "content" => $this->blocks[$blockname]["content"] . ob_get_contents(),
                        "mode" => $mode
                    );
                    break;
                case "after":
                case "append":
                    $this->blocks[$blockname] = array(
                        "content" => ob_get_contents() . $this->blocks[$blockname]["content"],
                        "mode" => $mode
                    );                                 
                    break;
            }
        }

        ob_end_clean();

        if($mode === "replace") {
            echo $this->blocks[$blockname]["content"];
        }
    }

    function cache($name, $ttl=3600) {
        $key = "template_".$name;
        $cache = apc_fetch($key, $success);
        if(!$success) {
            // randomize it a little bit (+/- 10%)
            // so when they don't all expires at the
            // same time
            $ttl += rand(-$ttl * .1, $ttl * .1);

            $this->caches[] = array($key, $ttl);
            ob_start("mb_output_handler");
            return $success;
        } else {
            return $cache;
        }
    }

    function endcache() {
        $data = ob_get_contents();

        ob_end_clean();

        list($key, $ttl) = array_pop($this->caches);
        apc_store($key, $data, $ttl);
        echo $data;
    }

    function __call($name, $arguments) {
        // take current context
        $base = $this->paths[count($this->paths) - 1];
        $path = dirname($name);
        if($path !== ".") {
            $base .= $path . "/";
            $name = basename($name);
        }
        // push current context
        $this->paths[] = $base;

        $file = $base.$name.$this->ext;
        if(file_exists($file)) {
            // prepare
            if(count($arguments) === 0) {
                $arguments[0] = array();
            }

            $args = array_merge((array)$this->globals, $arguments[0]);

            $this->file = $file;
            $this->inherits[$file] = array();
            // used by the placeholder
            array_unshift($this->args, $args);

            ob_start("mb_output_handler");
            extract($args);
            include($file);

            // restore args
            $args = array_shift($this->args);
            $content = ob_get_contents();
            ob_end_clean();

            while($inherit = array_pop($this->inherits[$file])) {
                $content = $this->{$inherit}($args);
            }

            // pop the context
            array_pop($this->paths);

            return $content;
        } else {
            throw new Exception("File not found ($file)");
        }
    }
}

?>