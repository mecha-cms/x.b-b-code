<?php

function fn_b_b_code($content, $lot = []) {
    if (!isset($lot['type']) || $lot['type'] !== 'BBCode') {
        return $content;
    }
    $x = new BBCode;
	$x->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    return str_replace("\n", '<br>', n($x->parse($content)->getAsHtml()));
}

function fn_b_b_code_span($content, $lot = []) {
    return t(fn_b_b_code($content, $lot), '<p>', '</p>');
}

From::plug('bbcode', function($input) {
    return fn_b_b_code($input, ['type' => 'BBCode']);
});

To::plug('bbcode', function($input) {
    return $input; // TODO
});

// alias(es)
From::plug('b_b_code', 'From::bbcode');
To::plug('b_b_code', 'To::bbcode');

Hook::set('*.title', 'fn_b_b_code_span', 2);
Hook::set(['*.description', '*.content'], 'fn_b_b_code', 2);