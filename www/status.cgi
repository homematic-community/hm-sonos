#!/usr/bin/env tclsh
source [file join [file dirname [info script]] sonos2inc.tcl] ;# Include-File
set content [loadFile status.html]
set zone ""
parseQuery
if [info exists args(zone)] {
    if {$args(zone) != "" && $args(zone) != "fehlt"} { set zone "?zone=$args(zone)"}
}
regsub -all {<%zone%>} $content  $zone content ;#" %>
puts "Content-type:text/html\n"
puts $content
