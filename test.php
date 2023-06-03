<?php

$content = file_get_contents(__DIR__ . D . 'test.txt');

echo fire("x\\b_b_code", [$content], (object) ['type' => 'BBCode']);

exit;