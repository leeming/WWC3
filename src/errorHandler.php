<?php
/*
* License: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or (at your
* option) any later version. This program is distributed in the hope that it
* will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
* of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
* Public License for more details.
*/

/**
 * Custom error handler for WWC. Bit basic at the moment, needs to be
 * added to, to make it a lot better :) E_ALL is the current error level
 *
 * TODO Better error handler needs to be added
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright  Copyright &copy; 2011, Leeming
 */

function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    //if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
    //    return;
    //}

    switch ($errno)
    {
    case E_USER_ERROR:
    case E_ERROR:
        $msg = "<b>ERROR</b> ";
        /*
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);*/
        break;

    case E_USER_WARNING:
    case E_WARNING:
        $msg = "<b>WARNING</b> ";
        //echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
    case E_NOTICE:
        $msg = "<b>NOTICE</b> ";
        //echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        $msg = "<b>UNKNOWN</b> ";
        //echo "Unknown error type: [$errno] $errstr IN $errfile:$errline<br />\n";
        break;
    }

    $msg .= "[$errno] IN $errfile:$errline :: $errstr<br />\n";

    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();

    $msg .= $trace."<br />\n";
    print $msg;
    //print"<pre>"; debug_print_backtrace(); print"</pre>";

    $dateFormat = date("[D M n H:i:s Y]", time());
    //error_log($dateFormat. " ". $msg, 3, "/var/www/wwc/src/php.error.log");

    /* Don't execute PHP internal error handler */
    return true;
}

error_reporting(E_ALL);
// set to the user defined error handler
$old_error_handler = set_error_handler("errorHandler");
?>
