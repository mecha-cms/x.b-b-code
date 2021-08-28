<?php

// Add `BBCode` field
Hook::set('_', function($_) {
    if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type'])) {
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['type']['lot']['BBCode'] = 'BBCode';
    }
    return $_;
});