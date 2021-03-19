<?php

if (0 === strpos($_['type'] . '/', 'page/') && 'get' === $_['form']['type']) {
    require __DIR__ . DS . '..' . DS . 'engine' . DS . 'r' . DS . 'hook.php';
}
