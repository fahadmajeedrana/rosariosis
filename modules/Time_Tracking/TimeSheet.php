<?php
require_once 'ProgramFunctions/FileUpload.fnc.php';
require_once 'modules/Time_Tracking/functions.inc.php';

//echo ErrorMessage( ['There are no grades available for this student.'], 'warning' );

if ( User( 'PROFILE' ) === 'teacher' ) //limit to teacher himself
{
	$_REQUEST['staff_id'] = User( 'STAFF_ID' );
}

if ( empty( $_REQUEST['print_statements'] ) )
{
	DrawHeader( ProgramTitle() );

	Search( 'staff_id', issetVal( $extra ) );
}

// Add eventual Dates to $_REQUEST['values'].
AddRequestedDates( 'values', 'post' );


if ( ! empty( $_REQUEST['values'] )
	&& $_POST['values']
	&& AllowEdit()
	&& UserStaffID() )
{
	foreach ( (array) $_REQUEST['values'] as $id => $columns )
	{		
		if ( $id !== 'new' )
		{			

			$time_total=_getTotalTimeForDay($columns['LOGGED_DATE'],$id);		
		
			if($time_total>0)
			{
				echo ErrorMessage( ['Time for '.$columns['LOGGED_DATE'].' already exists.'], 'warning' );
				continue;
			}
			if(!empty($columns['TIME']) && $columns['TIME']>24){
				echo ErrorMessage( ['Time for '.$columns['LOGGED_DATE'].' cannot be more than 24.'], 'warning' );
				continue;
			}

			$sql = "UPDATE timetracking_timesheets SET ";
	
			// if ( isset( $_FILES['FILE_ATTACHED_'.$id] ) )
			// {
			// 	echo 'FILE_ATTACHED_'.$id;
			// 	$columns['FILE_ATTACHED'] = FileUpload(
			// 		'FILE_ATTACHED_'.$id,
			// 		$FileUploadsPath . UserSyear() . '/staff_' . UserStaffID() . '/',
			// 		FileExtensionWhiteList(),
			// 		0,
			// 		$error
			// 	);

			// 	// Fix SQL error when quote in uploaded file name.
			// 	$columns['FILE_ATTACHED'] = DBEscapeString( $columns['FILE_ATTACHED'] );
			// }

			foreach ( (array) $columns as $column => $value )
			{
				$sql .= DBEscapeIdentifier( $column ) . "='" . $value . "',";
			}

			$sql = mb_substr( $sql, 0, -1 ) . " WHERE ID='" . $id . "'";

			DBQuery( $sql );
		}
		elseif ( $columns['TIME'] != ''
			&& $columns['LOGGED_DATE'] )
		{

			$time_total=_getTotalTimeForDay($columns['LOGGED_DATE'],$id);		
		
			if($time_total>0)
			{
				echo ErrorMessage( ['Time for '.$columns['LOGGED_DATE'].' already exists.'], 'warning' );
				continue;
			}
			if(!empty($columns['TIME']) && $columns['TIME']>24){
				echo ErrorMessage( ['Time for '.$columns['LOGGED_DATE'].' cannot be more than 24.'], 'warning' );
				continue;
			}

			$id = DBSeqNextID( 'timetracking_timesheets_id_seq' );

			$sql = "INSERT INTO timetracking_timesheets ";

			$fields = 'ID,STAFF_ID,SYEAR,SCHOOL_ID,';
			$values = "'" . $id . "','" . UserStaffID() . "','" . UserSyear() . "','" . UserSchool() . "',";

			if(empty($columns['COMMENTS']) || !$columns['COMMENTS']){
				$columns['COMMENTS']='-';
			}

			// if ( isset( $_FILES['FILE_ATTACHED_'] ) )
			// {
			// 	$columns['FILE_ATTACHED'] = FileUpload(
			// 		'FILE_ATTACHED_',
			// 		$FileUploadsPath . UserSyear() . '/staff_' . UserStaffID() . '/',
			// 		FileExtensionWhiteList(),
			// 		0,
			// 		$error
			// 	);

			// 	// Fix SQL error when quote in uploaded file name.
			// 	$columns['FILE_ATTACHED'] = DBEscapeString( $columns['FILE_ATTACHED'] );
			// }

			$go = 0;

			foreach ( (array) $columns as $column => $value )
			{
				if ( ! empty( $value ) || $value == '0' )
				{
					if ( $column == 'TIME' )
					{
						$value = preg_replace( '/[^0-9.-]/', '', $value );

						//FJ fix SQL bug invalid time

						if ( ! is_numeric( $value ) )
						{
							$value = 0;
						}
					}

					$fields .= DBEscapeIdentifier( $column ) . ',';
					$values .= "'" . $value . "',";
					$go = true;
				}
			}

			$sql .= '(' . mb_substr( $fields, 0, -1 ) . ') values(' . mb_substr( $values, 0, -1 ) . ')';

			if ( $go )
			{
				DBQuery( $sql );
			}
		}
	}

	// Unset values & redirect URL.
	RedirectURL( 'values' );
}

if ( $_REQUEST['modfunc'] === 'remove'
	&& AllowEdit() )
{
	if ( DeletePrompt( _( 'Timesheet Entry' ) ) )
	{
		// $file_attached = DBGetOne( "SELECT FILE_ATTACHED
		// 	FROM timetracking_timesheets
		// 	WHERE ID='" . $_REQUEST['id'] . "'" );

		// if ( ! empty( $file_attached )
		// 	&& file_exists( $file_attached ) )
		// {
		// 	// Delete File Attached.
		// 	unlink( $file_attached );
		// }

		DBQuery( "DELETE FROM timetracking_timesheets
			WHERE ID='" . $_REQUEST['id'] . "'" );

		// Unset modfunc & ID & redirect URL.
		RedirectURL( array( 'modfunc', 'id' ) );
	}
}

if ( UserStaffID() && ! $_REQUEST['modfunc'] )
{
	$functions = array(
		'REMOVE' => '_makeTimesheetRemove',
		'TIME' => '_makeTimesheetTextInput',
		'LOGGED_DATE' => '_makeTimesheetDateInput',
		'COMMENTS' => '_makeTimesheetCommentsInput',
		//'FILE_ATTACHED' => '_makeTimesheetFileInput',
	);

	$payments_RET = DBGet( "SELECT '' AS REMOVE,ID,TIME,LOGGED_DATE,COMMENTS--,FILE_ATTACHED
		FROM timetracking_timesheets
		WHERE STAFF_ID='" . UserStaffID() . "'
		AND SYEAR='" . UserSyear() . "'
		AND SCHOOL_ID='" . UserSchool() . "'
		ORDER BY ID", $functions );

	$i = 1;
	$RET = array();

	foreach ( (array) $payments_RET as $payment )
	{
		$RET[$i] = $payment;
		$i++;
	}

	if ( ! empty( $RET )
		&& empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		$columns = array( 'REMOVE' => '<span class="a11y-hidden">' . _( 'Delete' ) . '</span>' );
	}
	else
	{
		$columns = array();
	}

	$columns += array(
		'TIME' => _( 'Time' ),
		'LOGGED_DATE' => _( 'Date' ),
		'COMMENTS' => _( 'Comment' ),
	);

	// if ( empty( $_REQUEST['print_statements'] )
	// 	&& ! isset( $_REQUEST['_ROSARIO_PDF'] ) )
	// {
	// 	$columns += array( 'FILE_ATTACHED' => _( 'File Attached' ) );
	// }

	$link = array();

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		$link['add']['html'] = array(
			'REMOVE' => button( 'add' ),
			'TIME' => _makeTimesheetTextInput( '', 'TIME' ),
			'LOGGED_DATE' => _makeTimesheetDateInput( DBDate(), 'LOGGED_DATE' ),
			'COMMENTS' => _makeTimesheetCommentsInput( '', 'COMMENTS' ),
			//'FILE_ATTACHED' => _makeTimesheetFileInput( '', 'FILE_ATTACHED' ),
		);
	}

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname']  ) . '" method="POST">';
		DrawHeader( '', SubmitButton() );
		$options = array();
	}
	else
	{
		$options = array( 'center' => false, 'add' => false );
	}

	ListOutput( $RET, $columns, 'Time', 'Times', $link, array(), $options );

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '<div class="center">' . SubmitButton() . '</div>';
	}

	echo '<br />';

	$time_total = DBGetOne( "SELECT SUM(f.TIME) AS TOTAL
		FROM timetracking_timesheets f
		WHERE f.STAFF_ID='" . UserStaffID() . "'
		AND f.SYEAR='" . UserSyear() . "'
		AND f.SCHOOL_ID='" . UserSchool() . "'" );

	$table = '<table class="align-right"><tr><td>' . _( 'Total Time' ) . ': ' . '</td><td>' .  $time_total . '</td></tr></table>';

	DrawHeader( $table );

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '</form>';
	}
}
