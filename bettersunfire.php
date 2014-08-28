<?php

if(!isset($_GET['xxx'])){
    echo '.framebannerbg 		{ background-color: #C85424; background-image: url(images/bannerbg2.jpg); border-bottom: 1px solid #000000; }';
}

$components = array("xxx", "wcv", "rpz", "ans", "vti", "cnr", "smn", "cid");
foreach($components as $k => $c){
    if(isset($_GET[$c])){
        include __DIR__ . "/cmp/{$c}.css";
    }
}
