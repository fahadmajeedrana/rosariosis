<?php
/**
 * Time Tracking module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package RosarioSIS
 * @subpackage modules
 */

if ( $RosarioModules['Accounting'] ) // Verify Grades module is activated.
{
	$menu['Accounting']['admin'] += array(
		3 => _( 'Track Time' ),
		'Time_Tracking/TimeSheet.php' => dgettext( 'Track Time', 'Time Sheet' ),
	);

	$menu['Accounting']['teacher'] += array(
		3 => _( 'Track Time' ),
		'Time_Tracking/TimeSheet.php' => dgettext( 'Track Time', 'Time Sheet' ),
	);
}