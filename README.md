# logger
Another troubleshooting log file / debug tool - but with a twist

Everyone writes logging routines to help them troubleshoot problems or debug a program.
I've written a bunch over the years as well. But this one is a little different.

------------------------------------------------------------

(1) It is simple and easy to use. I got rid of the "log" as "class" weight.

(2) It is designed to sit in the stream of your program, active only when you want.

(3) It writes to file or screen

(4) With log files, it will only keep the most 'x' recent log files

(5) It can be used in production programs to log variables 'just in case'

------------------------------------------------------------

(A) Here is how EASY it is to use:

    - require_once('logger.php');  
    - logger($variable_1);  


(B) How about to a log file?

    - require_once('logger.php');  
    - logger_output_to_file();  
    - logger($variable_1);  


(C) What do you mean by 'sit in the stream'? It RETURNS BACK the first parm you give it!

	$fruit = logger( get_field('fruit') );  
    return logger($results);


(D) And you can keep logger in your program and turn it off when you don't need it.

    logger_off();


(E) You can log any PHP variable type.

    - The program will pretty-print both arrays and JSON strings  
    - The program will identify object classes and resource types


(F) You can log multiple items in one call:

    logger( $first_name, $last_name, $file_array, $active_flag );  


**PROGRAM OPTIONS**


    All options have defaults and are changed by calling a "logger_*()" function.

    logger_output_to_screen();     // THE DEFAULT  
    logger_output_to_file();

    logger_on();		// THE DEFAULT  
    logger_off();

    logger_timestamps(true);     prefix log records with date/time field  
    logger_timestamps(false);    no timestamps on log records   // THE DEFAULT

    logger_prefix($string);		print this string before each log record - DEFAULT is '>>>'  
    logger_suffix($string);		print this string after each log record - DEFAULT is ''

    logger_keep_versions(number); keep this many versions of disk logs  

    logger_directory($path);     put logs in this directory, no trailing directory delimiter  
                                 the directory must already exist - DEFAULT IS current directory './'

**LOG FILE NAMES**

    log files are named (directory)/(programname)-(yyyymmdd)-(hhmmss)(timezone).log

**QUESTIONS / IDEAS**

    Send me an email.
