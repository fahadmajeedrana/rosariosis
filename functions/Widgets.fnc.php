<?php
/**
 * (Student) Widgets function
 *
 * @package RosarioSIS
 * @subpackage functions
 */

/**
 * Widgets
 * Essentially used in the Find a Student form
 *
 * @since 5.1 Medical Immunization or Physical Widget.
 * @since 8.6 Use RosarioSIS\Widgets
 *
 * @global array   $_ROSARIO       Sets $_ROSARIO['SearchTerms']
 * @global array   $extra
 *
 * @param  string  $item           widget name or 'all' widgets.
 * @param  array   &$myextra       Search.inc.php extra (HTML, functions...) (optional). Defaults to global $extra.
 *
 * @return boolean False if insufficient rights, else true
 */
function Widgets( $item, &$myextra = null )
{
	global $extra,
		$_ROSARIO;

	static $widgets;

	// (Re)create it, if it's gone missing.
	if ( ! ( $widgets instanceof RosarioSIS\Widgets ) )
	{
		require_once 'classes/core/Widgets.php';
		require_once 'classes/core/Widget.php';

		$widgets = new RosarioSIS\Widgets();
	}

	// Do not use `! empty()` here.
	if ( isset( $myextra ) )
	{
		$extra =& $myextra;
	}

	$widgets->setExtra( $extra );

	// Fix PHP Fatal error unsupported operand types when Widgets() & $extra used for Student.
	$extra = $widgets->getExtra();

	// If insufficient rights, exit.
	if ( User( 'PROFILE' ) !== 'admin'
		&& User( 'PROFILE' ) !== 'teacher' )
	{
		return false;
	}

	switch ( $item )
	{
		// User Widgets (configured in My Preferences).
		case 'user':

			$user_widgets = ProgramUserConfig( 'WidgetsSearch' );

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
