##
# common.tcl
# Enthält gemeinsam verwendete Funktionen und Konstanten.
#
# @author F. Werner
##

#*******************************************************************************
# Allgemeine Konstanten
#*******************************************************************************

##
# Name des Addons.
##
set ADDON_NAME "sonos2"
#*******************************************************************************
# Logmeldungen
#*******************************************************************************

##
# Loggt eine Nachricht.
# @param message zu loggende Nachricht
##
proc log { message } {
	global ADDON_NAME

	exec logger "$ADDON_NAME - $message"
}

#*******************************************************************************
# Dateizugriff
#*******************************************************************************

##
# Speichert Daten in einer Datei.
# @param fileName Name der Datei
# @param Daten, die in die Datei geschrieben werden sollen
##
proc saveToFile { fileName content } {
  set fd -1

  set fd [open $fileName w]
  if { $fd != -1 } then {
    puts -nonewline $fd $content
    close $fd
  } else {
    error "could not write file $fileName"
  }
}

##
# Liefert den Inhalt einer Datei.
# @param fileName Name der Datei
# @return Inhalt der Datei
##
proc loadFromFile { fileName } {
  set fd -1
  
  set fd [open $fileName r]
  if { $fd != -1 } then {
    set result [read $fd]
  } else {
    error "could not read file $fileName"
  }
  
  return $result
}

#*******************************************************************************
# PID-Datei
#*******************************************************************************

##
# Name der PID-Datei.
##
set PID_FILE "$ADDON_NAME.pid"

##
# Ermittelt, ob die Zusatzsoftware gerade läuft.
# Es wird angenommen, dass die Zusatzsoftware läuft, solange die PID-Datei
# existiert.
# @return 1, falls die Zusatzsoftware gerade läuft
##
proc isRunning { } {
  global PID_FILE
  return [file exists $PID_FILE]
}

##
# Schreibt die PID-Datei.
# Die PID-Datei enthält die Prozess-Id der ausführenden Tcl-Instanz.
##
proc writePidFile { } {
  global PID_FILE
  
  saveToFile $PID_FILE [pid]
}

##
# Liefert die PID aus der PID-Datei.
# @return PID aus der PID-Datei
##
proc readPidFile { } {
  global PID_FILE
  
  return [loadFromFile $PID_FILE]
}

##
# Löscht die PID-Datei
##
proc removePidFile { } {
  global PID_FILE
  
  file delete $PID_FILE
}

