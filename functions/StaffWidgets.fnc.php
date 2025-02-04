<?php
/**
 * Staff Widgets function
 *
 * @package RosarioSIS
 * @subpackage functions
 */

/**
 * Staff Widgets
 * Essentially used in the Find a User form
 *
 * @since 8.6 Use RosarioSIS\StaffWidgets
 *
 * @global array   $_ROSARIO       Sets $_ROSARIO['SearchTerms']
 * @global array   $extra
 *
 * @param  string  $item           Staff widget name or 'all' Staff widgets.
 * @param  array   $myextra       Search.inc.php extra (HTML, functions...) (optional). Defaults to global $extra.
 *
 * @return boolean true if Staff Widget loaded, false if insufficient rights or already saved widget
 */
function StaffWidgets( $item, &$myextra = null )
{
	global $extra,
		$_ROSARIO;

	static $widgets;

	// (Re)create it, if it's gone missing.
	if ( ! ( $widgets instanceof RosarioSIS\StaffWidgets ) )
	{
		require_once 'classes/core/Widgets.php';
		require_once 'classes/core/StaffWidgets.php';
		require_once 'classes/core/StaffWidget.php';

		$widgets = new RosarioSIS\StaffWidgets();
	}

	// Do not use `! empty()` here.
	if ( isset( $myextra ) )
	{
		$extra =& $myextra;
	}

	$widgets->setExtra( $extra );

	// Fix PHP Fatal error unsupported operand types when StaffWidgets() & $extra used for Parent.
	$extra = $widgets->getExtra();

	// If insufficient rights, exit.
	if ( User('PROFILE') !== 'admin'
		&& User( 'PROFILE' ) !== 'teacher' )
	{
		return false;
	}

	switch ( (string) $item )
	{
		// User Widgets (configured in My Preferences).
		case 'user':

			$user_widgets = ProgramUserConfig( 'StaffWidgetsSearch' );

			foreach ( (array) $user_widgets as $user_widget_title => $value )
			{
				if ( $value )
				{
					$widgets->build( $user_widget_title );
				}
			}

		break;

		// All Widgets (or almost).
		case 'all':

		default:

			$widgets->build( $item );
	}

	$extra = $widgets->getExtra();

	if ( ! isset( $_ROSARIO['SearchTerms'] ) )
	{
		$_ROSARIO['SearchTerms'] = '';
	}

	$_ROSARIO['SearchTerms'] .= $widgets->getSearchTerms();

	return true;
}
