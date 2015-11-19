#!/bin/tclsh

##
# stop.tcl
# Stoppt die Zusatzsoftware.
#
# @author F. Werner
##

#*******************************************************************************
# Includes
#*******************************************************************************

source common.tcl

#*******************************************************************************
# Funktionen
#*******************************************************************************

##
# Hauptprogramm.
# Stoppt die Zusatzsoftware, sofern diese läuft.
##
proc main { } {
  if { [isRunning] } then {
    set pid [readPidFile]
    
    catch { exec kill -KILL $pid }
    removePidFile
  }

	log "stopped"
}

#*******************************************************************************
# Einsprungpunkt
#*******************************************************************************

if { [catch { main } errorMessage] } then {
  log $errorMessage
  exit 1
}

