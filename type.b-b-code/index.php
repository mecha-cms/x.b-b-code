<?php

namespace _\type\b_b_code {
    function i($content) {
        return \t(\_\type\b_b_code($content), '<p>', '</p>');
    }
    \Hook::set('*.title', __NAMESPACE__ "\\i", 2);
}

namespace _\type {
    function b_b_code($content) {
        if ($this['type'] !== 'BBCode') {
            return $content;
        }
        $parser = new \BBCode;
        $parser->addCodeDefinitionSet(new \JBBCode\DefaultCodeDefinitionSet());
        return \str_replace("\n", '<br>', \n($parser->parse($content)->getAsHtml()));
    }
    \Hook::set([
        '*.content',
        '*.description'
    ], __NAMESPACE__ . "\\b_b_code", 2);
}

namespace {
    require __DIR__ . DS . 'engine' . DS . 'i' . DS . 'Parser.php';
}