<?PHP // $Id: version.php,v 1.4 2009/05/28 12:28:36 diml Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of NEWMODULE
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2008051100;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2011000000;  // Requires this Moodle version
$module->cron     = HOURSECS * 2; // Period for cron to check this module (secs)

?>
