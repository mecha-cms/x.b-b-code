<?php

if (0 === strpos($_['layout'] . '.', 'page.')) {
    require __DIR__ . DS . '..' . DS . 'engine' . DS . 'r' . DS . 'hook.php';
}
