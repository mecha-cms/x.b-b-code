<?php

$content = file_get_contents(__DIR__ . D . 'test.txt');
$sub = trim(strtr(PATH . D, [rtrim(strtr($_SERVER['DOCUMENT_ROOT'], '/', D), D) . D => ""]), D);

echo strtr(fire("x\\b_b_code\\page__content", [$content], (object) ['type' => 'BBCode']) ?? "", [
    '://' . $_SERVER['HTTP_HOST'] . '/' => '://' . $_SERVER['HTTP_HOST'] . '/' . ("" !== $sub ? $sub . '/' : "")
]);

exit;