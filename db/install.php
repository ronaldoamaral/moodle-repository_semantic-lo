<?php

function xmldb_repository_semantic_lo_install() {
    global $CFG;
    $result = true;
    require_once($CFG->dirroot.'/repository/lib.php');
    $semantic_loplugin = new repository_type('semantic_lo', array(), true);
    if (!$id = $semantic_loplugin->create(true)) {
        $result = false;
    }
    return $result;
}