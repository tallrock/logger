<?php
/**
 * LOGGER - yet another troubleshooting log file / debug tool -with a twist
 *
 * Everyone writes logging routines to help them troubleshoot problems or debug a program.
 * I've written a bunch over the years as well. But this one is a little different.
 *
 * (1) It is simple and easy to use. I got rid of the "log" as "class" weight.
 * (2) It is designed to sit in the stream of your program, active only when you want.
 * (3) It writes to file or screen
 * (4) With log files, it will only keep the most 'x' recent log files
 * (5) It can be used in production programs to log variables 'just in case'
 *
 *
 * (A) Here is how EASY it is to use:
 *
 *     require_once('logger.php');
 *     logger($variable_1);
 *
 * (B) How about to a log file?
 *
 *     require_once('logger.php');
 *     logger_output_to_file();
 *     logger($variable_1);
 *
 * (C) What do you mean by 'sit in the stream'? It RETURNS BACK the first parm you give it!
 *
 * 	   $fruit = logger( get_field('fruit') );
 *     return logger($results);
 *
 * (D) And you can keep logger in your program and turn it off when you don't need it.
 *
 *     logger_off();
 *
 * (E) You can log any PHP variable type.
 *     - The program will pretty-print both arrays and JSON strings
 *     - The program will identify object classes and resource types
 *
 * (F) You can log multiple items in one call:
 *
 *     logger( $first_name, $last_name, $file_array, $active_flag );
 *
 *
 * PROGRAM OPTIONS
 *
 *     All options have defaults and are changed by calling a "logger_*()" function.
 *
 *     logger_output_to_screen();     // THE DEFAULT
 *     logger_output_to_file();
 *
 *     logger_on();		// THE DEFAULT
 *     logger_off();
 *
 *     logger_timestamps(true);     prefix log records with date/time field
 *     logger_timestamps(false);    no timestamps on log records   // THE DEFAULT
 *
 *     logger_prefix($string);		print this string before each log record - DEFAULT is '>>>'
 *     logger_suffix($string);		print this string after each log record - DEFAULT is ''
 *
 *     logger_keep_versions(number); keep this many versions of disk logs
 *
 *     logger_directory($path);     put logs in this directory, no trailing directory delimiter
 *                                  the directory must already exist - DEFAULT IS current directory './'
 *
 *
 * LOG FILE NAMES
 *
 *     log files are named (directory)/(programname)-(yyyymmdd)-(hhmmss)(timezone).log
 *
 * QUESTIONS / IDEAS
 *
 *     Send me an email.
 *
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author     James DeRocher <jdd@tallrock.com>
 * @copyright  James DeRocher, 2017
 * @version    0.5
 *
 */

//
//  GLOBAL VARIABLES
//
$logger_directory = '.';
$logger_keep_versions = 1;
$logger_on = 'yes';
$logger_output_direction = 'screen';
$logger_prefix = '>>>';
$logger_suffix = '';
$logger_timestamps = false;

//
// LOGGER SWITCHES - ONE-TIME AND FIRST-TIME FLAGS
//
$logger_deleted_old_versions = false;

//
// LOG FILE NAME - THE PARTS
//
$logger_rundate = date('Ymd-HisT');
$logger_program = pathinfo($_SERVER['PHP_SELF'])['filename'];
$logger_filename = $logger_directory.'/'.$logger_program.'-'.$logger_rundate.'.log';


//
// LOGGER
//

function logger(...$parms)
{
	global $logger_on, $logger_deleted_old_versions;

	if ( $logger_deleted_old_versions === false ) {
		$logger_deleted_old_versions = true;
		_logger_delete_old_versions();
	}

	if ( $logger_on == 'yes' )
	{
		foreach ($parms as $parm)
		{
			switch (gettype($parm)) {
			case 'boolean':
				_logger_print( ( $parm === true ? "true" : "false" ) );
				break;

			case 'integer':
				_logger_print( (string)$parm );
				break;

			case 'double':
				_logger_print( (string)$parm );
				break;

			case 'string':
				$str = trim($parm);
				// IF THIS IS A JSON STRING, BE NICE AND DECODE IT BEFORE PRINTING
				if ( ( substr($str,0,1) == '{' and substr($str,-1) == '}' ) ||
				     ( substr($str,0,1) == '[' and substr($str,-1) == ']' ) ) {
					$json = json_decode($str,true);
					if ( is_array($json) ) {
						_logger_print( "JSON " . print_r( $json, true ));
						break;
					}
				}
				_logger_print( $parm );
				break;

			case 'array':
				_logger_print( print_r( $parm, true ));
				break;

			case 'object':
				_logger_print( "object(".get_class($parm).")" );
				break;

			case 'resource':
				_logger_print( "resource(".get_resource_type($parm).")" );
				break;

			case 'NULL':
				_logger_print( "NULL" );
				break;

			case 'unknown type':
				_logger_print( "unknown type" );
				break;

			default:
				_logger_print($parm);
				break;
			}
		}
	}

	if ( sizeof($parms) == 1 ) return $parms[0];
	return $parms;
}

//
// INTERNAL FUNCTION - PRINT TO FILE OR PRINT TO SCREEN
//

function _logger_print( $str )
{
	global $logger_timestamps, $logger_prefix, $logger_suffix, $logger_output_direction, $logger_filename;
	$ts = ( $logger_timestamps === true ? date('Ymd-HisT') : '' );
	$line = $ts.$logger_prefix.$str.$logger_suffix."\n";
	if ( $logger_output_direction == 'file' ) {
		$fp = fopen( $logger_filename, 'a' );
		if ( $fp !== false ) {
			fwrite($fp,$line);
		    fclose($fp);
		}
	} else {
		print($line);
	}
}

//
// INTERNAL FUNCTION - DELETE OLD LOG FILES (and log that we've done so)
//

function _logger_delete_old_versions()
{
	global $logger_directory, $logger_program, $logger_keep_versions;
	if ( $logger_keep_versions < 1 ) return;
	$files = glob($logger_directory.'/'.$logger_program.'*.log');
	$nbr = sizeof($files) - ($logger_keep_versions-1);
	for ($i=0; $i<$nbr ; $i++) {
		unlink( $files[$i] );
		logger("LOGGER: Deleted old log file ".$files[$i]);
	}
}

//
// ALL THE WAYS YOU HAVE TO CUSTOMIZE THE LOGGING PROCESS
//

function logger_output_to_file() {
    global $logger_output_direction;
    $logger_output_direction = 'file';
	}
function logger_on() {
    global $logger_on;
    $logger_on = 'yes';
	}
function logger_off() {
    global $logger_on;
    $logger_on = 'no';
	}
function logger_output_to_screen() {
	global $logger_output_direction;
	$logger_output_direction = 'screen';
	}
function logger_directory($name) {
	global $logger_directory;
	if ( gettype($name)=='string' && is_dir($name) ) $logger_directory = $name;
	}
function logger_keep_versions($nbr) {
	global $logger_keep_versions;
	if ( gettype($nbr) =='integer' && $nbr > 0 and $nbr < 100 ) $logger_keep_versions = $nbr;
	}
function logger_timestamps($bool) {
	global $logger_timestamps;
	if ( gettype($bool)=='boolean' ) $logger_timestamps = $bool;
    }
function logger_prefix($code) {
	global $logger_prefix;
	if ( gettype($code)=='string' ) $logger_prefix = $code;
	}
function logger_suffix($code) {
	global $logger_suffix;
	if ( gettype($code)=='string' ) $logger_suffix = $code;
	}

//
// THATS ALL THERE IS TO IT
//
?>
