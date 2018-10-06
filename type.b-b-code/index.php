<?php

function fn_b_b_code($content, $lot = [], $that) {
    if ($that->get('type') !== 'BBCode') {
        return $content;
    }
    $x = new BBCode;
    $x->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    return str_replace("\n", '<br>', n($x->parse($content)->getAsHtml()));
}

function fn_b_b_code_span($content, $lot = []) {
    return t(fn_b_b_code($content, $lot), '<p>', '</p>');
}

From::_('BBCode', function($input) {
    return fn_b_b_code($input, ['type' => 'BBCode']);
});

To::_('BBCode', function($input) {
    return $input; // TODO
});

// alias(es)
From::_('bbcode', 'From::BBCode');
To::_('bbcode', 'To::BBCode');

Hook::set('*.title', 'fn_b_b_code_span', 2);
Hook::set(['*.description', '*.content'], 'fn_b_b_code', 2);