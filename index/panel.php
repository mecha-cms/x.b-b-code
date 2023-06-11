<?php namespace x\b_b_code;

// Add `BBCode` field
function _($_) {
    if (0 !== \strpos($_['type'] . '/', 'page/') || !isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type'])) {
        return $_;
    }
    $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type']['lot']['BBCode'] = 'BBCode';
    return $_;
}

\Hook::set('_', __NAMESPACE__ . "\\_");