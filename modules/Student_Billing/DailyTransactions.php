<?php
/**
 * Daily Transactions
 *
 * @since 8.0 Merge Daily Transactions & Daily Totals programs
 *
 * @package RosarioSIS
 * @subpackage modules
 */

DrawHeader( ProgramTitle() );

$_REQUEST['program'] = issetVal( $_REQUEST['program'], '' );

if ( $_REQUEST['program'] === 'totals'
	&& User( 'PROFILE' ) === 'admin' )
{
	require_once 'modules/Student_Billing/includes/DailyTotals.php';
}
else
{
	require_once 'modules/Student_Billing/includes/DailyTransactions.php';
}


/**
 * Program Menu
 *
 * Local function
 *
 * @since 8.0
 *
 * @param  string $program Program: transactions|totals.
 *
 * @return string           Select Program input.
 */
function _programMenu( $program )
{
	$link = PreparePHP_SELF(
		array(),
		array( 'program' )
	) . '&program=';

	$menu = SelectInput(
		$program,
		'program',
		'',
		array(
			'transactions' => _( 'Daily Transactions' ),
			'totals' => _( 'Daily Totals' ),
		),
		false,
		'onchange="ajaxLink(\'' . $link . '\' + this.value);" autocomplete="off"',
		false
	);

	return $menu;
}
