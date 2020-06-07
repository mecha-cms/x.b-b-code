<?php namespace _\lot\x\b_b_code\panel\page;

function fields($_) {
    // Add `BBCode` field
    if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type'])) {
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type']['lot']['BBCode'] = 'BBCode';
    }
    return $_;
}

\Hook::set('_', __NAMESPACE__ . "\\fields");
