<?php
/**
 * Options for the clipauth plugin
 *
 * @author Tongyu Nie <marktnie@gmail.com>
 */


//$meta['fixme'] = array('string');
$meta['editperpage']      = array('numericopt');
$meta['commentperpage']   = array('numericopt');
$meta['needInvitation'] = array('onoff');
$meta['invitationCodeLen'] = array('numericopt');
$meta['usernameMaxLen'] = array('numericopt');
$meta['passMinLen'] = array('numericopt');
$meta['passMaxLen'] = array('numericopt');
$meta['fullnameMaxLen'] = array('numericopt');
$meta['editors'] = array('string');
$meta['resultperpage'] = array('numericopt');
