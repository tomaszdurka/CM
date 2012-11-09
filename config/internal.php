<?php
// This is autogenerated action verbs config file. You should not adjust changes manually.
// You should adjust TYPE constants and regenerate file using `generate-config.php`

if (!isset($config->CM_Site_Abstract)) {
	$config->CM_Site_Abstract = new StdClass();
}
$config->CM_Site_Abstract->types = array();
$config->CM_Site_Abstract->types[CM_Site_CM::TYPE] = 'CM_Site_CM'; // #1
// Highest type used: #1

if (!isset($config->CM_Action_Abstract)) {
	$config->CM_Action_Abstract = new StdClass();
}
$config->CM_Action_Abstract->types = array();
// Highest type used: #0

if (!isset($config->CM_Model_Abstract)) {
	$config->CM_Model_Abstract = new StdClass();
}
$config->CM_Model_Abstract->types = array();
$config->CM_Model_Abstract->types[CM_Model_DeviceCapabilities::TYPE] = 'CM_Model_DeviceCapabilities'; // #20
$config->CM_Model_Abstract->types[CM_Model_Language::TYPE] = 'CM_Model_Language'; // #23
$config->CM_Model_Abstract->types[CM_Model_Location::TYPE] = 'CM_Model_Location'; // #24
$config->CM_Model_Abstract->types[CM_Model_SmileySet::TYPE] = 'CM_Model_SmileySet'; // #15
$config->CM_Model_Abstract->types[CM_Model_Splittest::TYPE] = 'CM_Model_Splittest'; // #16
$config->CM_Model_Abstract->types[CM_Model_Splittest_RequestClient::TYPE] = 'CM_Model_Splittest_RequestClient'; // #26
$config->CM_Model_Abstract->types[CM_Model_Splittest_User::TYPE] = 'CM_Model_Splittest_User'; // #27
$config->CM_Model_Abstract->types[CM_Model_SplittestVariation::TYPE] = 'CM_Model_SplittestVariation'; // #17
$config->CM_Model_Abstract->types[CM_Model_Stream_Publish::TYPE] = 'CM_Model_Stream_Publish'; // #21
$config->CM_Model_Abstract->types[CM_Model_Stream_Subscribe::TYPE] = 'CM_Model_Stream_Subscribe'; // #22
$config->CM_Model_Abstract->types[CM_Model_StreamChannel_Message::TYPE] = 'CM_Model_StreamChannel_Message'; // #18
$config->CM_Model_Abstract->types[CM_Model_StreamChannel_Video::TYPE] = 'CM_Model_StreamChannel_Video'; // #19
$config->CM_Model_Abstract->types[CM_Model_StreamChannelArchive_Video::TYPE] = 'CM_Model_StreamChannelArchive_Video'; // #25
$config->CM_Model_Abstract->types[CM_Model_User::TYPE] = 'CM_Model_User'; // #13
// Highest type used: #27

if (!isset($config->CM_Model_ActionLimit_Abstract)) {
	$config->CM_Model_ActionLimit_Abstract = new StdClass();
}
$config->CM_Model_ActionLimit_Abstract->types = array();
// Highest type used: #0

if (!isset($config->CM_Model_Entity_Abstract)) {
	$config->CM_Model_Entity_Abstract = new StdClass();
}
$config->CM_Model_Entity_Abstract->types = array();
// Highest type used: #0

if (!isset($config->CM_Model_StreamChannel_Abstract)) {
	$config->CM_Model_StreamChannel_Abstract = new StdClass();
}
$config->CM_Model_StreamChannel_Abstract->types = array();
$config->CM_Model_StreamChannel_Abstract->types[CM_Model_StreamChannel_Message::TYPE] = 'CM_Model_StreamChannel_Message'; // #18
$config->CM_Model_StreamChannel_Abstract->types[CM_Model_StreamChannel_Video::TYPE] = 'CM_Model_StreamChannel_Video'; // #19
// Highest type used: #19

if (!isset($config->CM_Mail)) {
	$config->CM_Mail = new StdClass();
}
$config->CM_Mail->types = array();
$config->CM_Mail->types[CM_Mail_Welcome::TYPE] = 'CM_Mail_Welcome'; // #2
// Highest type used: #2

if (!isset($config->CM_Paging_Log_Abstract)) {
	$config->CM_Paging_Log_Abstract = new StdClass();
}
$config->CM_Paging_Log_Abstract->types = array();
$config->CM_Paging_Log_Abstract->types[CM_Paging_Log_Error::TYPE] = 'CM_Paging_Log_Error'; // #1
$config->CM_Paging_Log_Abstract->types[CM_Paging_Log_Mail::TYPE] = 'CM_Paging_Log_Mail'; // #3
// Highest type used: #3

if (!isset($config->CM_Paging_ContentList_Abstract)) {
	$config->CM_Paging_ContentList_Abstract = new StdClass();
}
$config->CM_Paging_ContentList_Abstract->types = array();
$config->CM_Paging_ContentList_Abstract->types[CM_Paging_ContentList_Badwords::TYPE] = 'CM_Paging_ContentList_Badwords'; // #2
// Highest type used: #2


if (!isset($config->CM_Action_Abstract)) {
	$config->CM_Action_Abstract = new StdClass();
}
$config->CM_Action_Abstract->verbs = array();