<?php
function _makeTimesheetRemove( $value, $column )
{
	global $THIS_RET;

	return button(
		'remove',
		_( 'Delete' ),
		'"' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=remove&id=' . $THIS_RET['ID'] ) . '"'
	);
}
function _makeTimesheetDateInput( $value, $column )
{
	global $THIS_RET;

	if ( ! empty( $THIS_RET['ID'] ) )
	{
		$id = $THIS_RET['ID'];
	}
	else
		$id = 'new';

	return DateInput( $value, 'values[' . $id . '][' . $column . ']', '', ( $id !== 'new' ), false );
}
function _makeTimesheetTextInput( $value, $name )
{
	global $THIS_RET;

	if ( ! empty( $THIS_RET['ID'] ) )
	{
		$id = $THIS_RET['ID'];
	}
	else
		$id = 'new';

	$extra = 'maxlength=255';

	if ( $name === 'AMOUNT' )
	{
		$extra = ' type="number" step="any"';
	}
	elseif ( ! $value )
	{
		$extra .= ' size=15';
	}

	return TextInput( $value, 'values[' . $id . '][' . $name . ']', '', $extra );
}


/**
 * Make Payments Comments Input
 * Add Salaries dropdown to reconcile Payment:
 * Automatically fills the Comments & Amount inputs.
 *
 * @since 5.1
 * @since 7.7 Remove Salaries having a Payment (same Amount & Comments (Title), after or on Assigned Date).
 *
 * @uses _makePaymentsTextInput()
 *
 * @param  string $value Comments value.
 * @param  string $name  Column name, 'COMMENTS'.
 *
 * @return string Text input if not new or if no Salaries found, else Text input & Salaries dropdown.
 */
function _makeTimesheetCommentsInput( $value, $name )
{
	global $THIS_RET;

	$text_input = _makeTimesheetTextInput( $value, $name );
	return $text_input;

}

function _makeTimesheetFileInput( $value, $column )
{
	global $THIS_RET;

	if ( empty( $THIS_RET['ID'] ) || empty( $value )
	|| ! file_exists( $value ) )
	{
		return FileInput(
			'FILE_ATTACHED_'.$THIS_RET['ID']
		);
	}

	$file_path = $value;

	$file_name = mb_substr( mb_strrchr( $file_path, '/' ), 1 );

	$file_size = HumanFilesize( filesize( $file_path ) );

	// Truncate file name if > 36 chars.
	$file_name_display = mb_strlen( $file_name ) <= 36 ?
		$file_name :
		mb_substr( $file_name, 0, 30 ) . '..' . mb_strrchr( $file_name, '.' );

	$file = button(
		'download',
		$file_name_display,
		'"' . URLEscape( $file_path ) . '" target="_blank" title="' . $file_name . ' (' . $file_size . ')"',
		'bigger'
	);

	return $file;
}

function _getTotalTimeForDay($date,$id)
{

	$sql="SELECT SUM(f.TIME) AS TOTAL
	FROM timetracking_timesheets f
	WHERE f.LOGGED_DATE='" . $date . "'
	AND f.STAFF_ID='" . UserStaffID() . "'
	AND f.SYEAR='" . UserSyear() . "'
	AND f.SCHOOL_ID='" . UserSchool() . "'";

	if($id!='new' && !empty($id)){
		$sql.="AND f.id !='".$id."'";
	}

	$time_total = DBGetOne($sql);
	return $time_total+(empty($time)?0:$time);
}