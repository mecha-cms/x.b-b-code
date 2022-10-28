<?php

$content = file_get_contents(__DIR__ . D . 'test');

echo fire("x\\b_b_code", [$content], (object) ['type' => 'BBCode']);

exit;