#!/usr/bin/env tclsh
source [file join [file dirname [info script]] sonos2inc.tcl] ;# Include-File
#####################################################################################
#
# tlc version in Anlehnung an PHP-Version, Device-Spy und Wireshark
# Datum diesr Version:  28.05.2015
# Veröffentlicht im Forum: http://homematic-forum.de
# Username: fiveyears
# 
# Scriptaufruf:  /usr/local/etc/config/addons/www/sonos2/sonos2.cgi zone action parameter
#    z. B.:      /usr/local/etc/config/addons/www/sonos2/sonos2.cgi tv volume 30
# 
# Grundlegender URL Aufbau:
# -------------------------
# sonos2.cgi?zonen=SONOSPLAYER&action=BEFEHL&BEFEHL=Option
# Beispiele:
# sonos2.cgi?zonen=buero&action=play                        -> Absielen Starten
# sonos2.cgi?zonen=buero&action=pause                       -> Abspielen Pausieren
# sonos2.cgi?zonen=buero&action=next                        -> Nächster Titel
# sonos2.cgi?zonen=buero&action=previous                    -> Verheriger Titel
# sonos2.cgi?zonen=buero&action=mute&mute=false             -> Sonos Laut schalten - false
# sonos2.cgi?zonen=buero&action=mute&mute=true              -> Sonos Leise schalten - true
# sonos2.cgi?zonen=buero&message=1&volumen=20               -> Nachricht 1 anspielen mit Lautstärke 20
# sonos2.cgi?zonen=buero&action=titelinfo                   -> einfache HTML Playlist Anzeige
# sonos2.cgi?zonen=buero&action=addmember&member=kueche     -> Zone Kueche zur Zone Buero hinzufügen
# sonos2.cgi?zonen=buero&action=removemember&member=kueche  -> Zone Kueche aus der Zone Buero entfernen
# sonos2.cgi?zonen=schlafzimmer&action=sonosplaylist&playlist=Einschlafmusik&volume=1
# sonos2.cgi?player=schlafzimmer&action=stop
# sonos2.cgi?player=tv-zimmer&action=addmember&member=wohnzimmer
#
#
# zonen=       (wohnzimmer,tv,bad,kueche,schlafzimmer)   -> kleinschreibung, keine Leer oder Sonderzeichen
# player=      (wohnzimmer,tv,bad,kueche,schlafzimmer)   -> statt zonen geht auch player und zone
#     
# action=                     
#     play                                      -> Abspielen
#                    url=<any valid sonos url>  -> Abspielen einer gegebenen URL z. B.  x-file-cifs://HOSTNAME/sharename/song.mp3
#     pause                                     -> Pause
#     stop                                      -> Stop
#     toggle                                    -> Play / Pause umschalten
#     next                                      -> Next track in playlist
#     previous                                  -> Previous track in playlist
#     rewind                                    -> zum Anfang des tracks
#     settrack                                  -> set track in playlist
#     seek                                      -> seek relative in track
#     mute           (true,false)               -> Stumm schalten
#     shuffle        (true,false)               -> shuffle an/aus
#     repeat         (true,false)               -> repeat an/aus
#     crossfade      (true,false)               -> Titelübergang aus, aus
#     volume         (0-100)                    -> Lautstärke setzen
#     ramp           (0-100)                    -> auto ramp to Volume
#     sleep          (0-100)                    -> sleep ramp to Volume
#     alarm          (0-100)                    -> alarm ramp to Volume
#     volumeup                                  -> Lautstärke um 3% erhöhen
#     volumedown                                -> Lautstärke um 5% verringern
#     addMember                                 -> fügt Player zu Gruppe hinzu
#                     member=Playername
#     removeMember                              -> entfernt Player aus Gruppe
#                     member=Playername
#     partymodus                                -> alle Player in eine Gruppe
#
#     message                                   -> entfernt Player aus Gruppe
#                     message=file.ext          -> z. B. action=message&message=Hallo.m4a
#                     message=file.ext          -> z. B. action=message&message=1.mp3
#
#     info            (Info-Name)               -> zeigt Infos an
#                     AudioInputAttributes   
#                     ZoneGroupAttributes  
#                     ZoneAttributes  
#                     ZoneInfo    
#                     Alarm                     -> Liste der Alarme, nicht formatiert
#                     RadioIdUri                -> ID: gut zum Anlegen neuer Sender und Radio-URI
#                     MediaPositionInfo         -> z. B. Sonos-URI, Sendername
#                     CurrentPlaylist           
#                     mute    
#                     crossfademode    
#                     repeat    
#                     shuffle    
#                     volume    
#                     LEDState    
#                     AskRadio                  -> Info, was läuft
#                          
######## Anzupassender Bereich #######################################################

# Konfiguration lesen

set message ""
Cfg::Load
######## Script Code #######################################################
parseQuery
if {[info exists args(zone)]} {
   if { ! [sonosCreate $args(zone)] } {
      set sonosArray(Zone) "Sonoszone '$args(zone)' nicht gefunden!\n"
   } else {
      if {[info exists args(action)]} {
         set action $args(action)
         switch $action {
            message {
               if {[info exists args(message)]} {
                  set vol $Cfg::messagevolume
                  if {[info exists args(volume)]} {
                     if {[ string is double $args(volume) ]} {
                        set vol [ expr round($args(volume)) ]
                        if { $vol > 100 } { set vol 100 } elseif { $vol < 0 } { set vol 0 }
                     }
                  }
                  playMessage $args(message) $vol
               }
            }
            partymodus {
               setPartymodus
               
            }
            addmember {
               if {[info exists args(member)]} {
                  addMember $args(member)
               }
            }
            removemember {
               if {[info exists args(member)]} {
                  removeMember $args(member)
               }
            }
            radio {
               if {[info exists args(radio)]} {
                  SetRadio $args(radio)
                  GetPositionInfo
                  Play
               }
            }
            mute {
               set mute [GetMute]
               if {[info exists args(mute)]} {
                  set mute $args(mute)
               } else {
                  if { $mute == "1" } {
                     set mute 0
                  } {
                     set mute 1
                  }         }
               SetMute $mute
            }
            repeat {
               GetTransportSettings
               set shuffle [sonosGet shuffle]
               if {[info exists args(repeat)]} {
                  set repeat $args(repeat)
               } else {
                  set repeat 0
               }
               SetPlayMode $repeat $shuffle
            }
            shuffle {
               GetTransportSettings
               set repeat [sonosGet repeat]
               if {[info exists args(shuffle)]} {
                  set shuffle $args(shuffle)
               } else {
                  set shuffle 0
               }
               SetPlayMode $repeat $shuffle
            }
            crossfade {
               if {[info exists args(crossfade)]} {
                  set crossfade $args(crossfade)
               } else {
                  set crossfade ""
               }
               SetCrossfadeMode $crossfade
            }
            volume {
               if {[info exists args(volume)]} {
                  set volume $args(volume)
               } else {
                  set volume $Cfg::stdvolume
               }
               SetVolume $volume
            }
            ramp {
               if {[info exists args(ramp)]} {
                  set volume $args(ramp)
               } else {
                  set volume $Cfg::stdvolume
               }
               RampToVolume $volume [sonosGet Rampto]
            }
            sleep {
               if {[info exists args(sleep)]} {
                  set volume $args(sleep)
               } else {
                  set volume $Cfg::stdvolume
               }
               RampToVolume $volume sleep
            }
            alarm {
               if {[info exists args(alarm)]} {
                  set volume $args(alarm)
               } else {
                  set volume $Cfg::stdvolume
               }
               RampToVolume $volume alarm
            }
            volumeup {
               VolumeUp
            }
            volumedown {
               VolumeDown
            }
            play {
               if {[info exists args(url)]} {
                  SetAVTransportURI $args(url)
               }
               Play
            }
            pause {
               Pause
            }
            stop {
               Stop
            }
            seek {
               if {[info exists args(relpos)]} {
                  Seek $args(relpos) "NONE"
               }
            }
            next {
               Next
            }
            previous {
               Previous
            }
            settrack {
               if {[info exists args(tracknr)]} {
                  SetTrack $args(tracknr)
               }
            }
            rewind {
               Rewind
            }
            toggle {
               Toggle
            }
            volumedown {
               VolumeDown
            }
            volumedown {
               VolumeDown
            }
            udp {
               set Cfg::sonoszone [Udp]
               Cfg::Save
            }
         }
      }
   }
   if [regexp ".*nicht gefunden.*" $sonosArray(Zone)] {
      set message [getMessage $sonosArray(Zone) danger]
      set sonosArray(Zone) "fehlt"
   } else {
      GetZoneAttributes
      if [info exists info(CurrentZoneName)] {
         set sonosArray(CurrentZoneName) $info(CurrentZoneName)
         init
         set player "${h1}Player: $sonosArray(CurrentZoneName)$unh1$hr"
      } else {
         set message [getMessage "Sonoszone '$sonosArray(Zone)' mit IP '$sonosArray(IP)' nicht erreichbar!" danger]
         set sonosArray(Zone) "fehlt"
      }
   }
} {
   set message [getMessage "Keine Sonoszone angegeben!\n" info]
   set sonosArray(Zone) "fehlt"
}
if {$sonosArray(Zone) == "fehlt"} {
   init $message
   pBr "${h2}Sonosscript für die CCU2$unh2"
   pBr "${pre}Scriptaufruf:  ${bold}/usr/local/etc/config/addons/www/sonos2/sonos2.cgi zone action parameter$unbold"
   pBr "   Beispiel1:  /usr/local/etc/config/addons/www/sonos2/sonos2.cgi tv play"
   pBr "   Beispiel2:  /usr/local/etc/config/addons/www/sonos2/sonos2.cgi tv volume 30"
   pBr "Browseraufruf: http://homematic-ccu2/addons/sonos2/sonos2.cgi?zone=${bold}ZONE$unbold&action=${bold}ACTION$unbold\[&ACTION=${bold}PARAMETER$unbold\]"
   pBr "    Beispiel1: http://homematic-ccu2/addons/sonos2/sonos2.cgi?zone=tv&action=play"
   pBr "    Beispiel2: http://homematic-ccu2/addons/sonos2/sonos2.cgi?zone=tv&action=volume&volume=30"
	pBr "Die Sonoszone (der Player) muss immer angegeben werden!$unpre"
   theEnd
	exit	
}

GetSonosUUID

if { [info exists info] } {unset info}
if [info exists sonosArray(CurrentZoneName)] {
   unset sonosArray(Zone)
}
if {[info exists args(action)]} {
   set action $args(action)
   switch $action {
      message -
      partymodus -
      addmember -
      removemember -
      radio -
      mute -
      repeat -
      shuffle -
      crossfade -
      volume -
      ramp -
      sleep -
      alarm -
      volumeup -
      volumedown -
      play -
      pause -
      stop -
      seek -
      next -
      previous -
      settrack -
      rewind -
      stop -
      stop -
      toggle -
      volumedown -
      volumedown -
      udp -
      info {
         if {[info exists args(info)]} {
            switch  -glob -- $args(info) {
               audioinputattribute* {
                  pBr $player
                 GetAudioInputAttributes
                  listArray  info  "Audio-Inputattributes"
                  listArray  sonosArray  "Player-Info"
               }
               zoneattribute* {
                  GetZoneAttributes
                  pBr $player
                  listArray info "Zone-Attributes"
                  listArray  sonosArray  "Player-Info"
               }
               zonegroupattribute* {
                  pBr $player
                  GetZoneGroupAttributes
                  listArray info "Zonegroup-Attributes"
                  listArray  sonosArray  "Player-Info"
               }
               zoneinf* {
                  GetZoneInfo
                  pBr $player
                  listArray info "Zone-Info"
                  listArray  sonosArray  "Player-Info"
               }
               transportinf* {
                  GetTransportInfo
                  pBr $player
                  listArray info "Transport-Info"
                  GetTransportSettings
                  listArray  sonosArray  "Player-Info"
               }
               env {
                  pBr $player
                  listArray env
                  listArray  sonosArray  "Player-Info"
               }
               alar* {
                  pBr $player
                  ListAlarms
                  listArray info "Alarm-List"
                  listArray sonosArray  "Player-Info"
               }
               radioi* {
                  pBr $player
                  set id [IsRadio]
                  if { $id == "0" } {
                     List2Values "Attention:" "This is no radio station or id not found!"
                  } {
                     List2Values "Radio-ID:" $id
                     List2Values "RadioURI:" [GetRadioUri]
                  }
                  listArray  sonosArray  "Player-Info"
               }
               positionmediainf* {
                  pBr $player
                  GetMediaInfo
                  listArray  info "Media-Info"
                  unset info
                  GetPositionInfo ; 
                  #GetCurrentPlaylist;
                  listArray info "Position Info"
                  listArray  sonosArray  "Player-Info"
               }
               currentplayli* {
                  pBr $player
                  GetCurrentPlaylist;
                  listPlaylist playlist "Current Playlist &nbsp; ($playlistcount Einträge)"
                  listArray  sonosArray  "Player-Info"
               }
               sonosplayli* {
                  pBr $player
                  GetSonosPlaylists;
                  listPlaylist playlist "Sonos Playlists &nbsp; ($playlistcount Einträge)"
                  listArray  sonosArray  "Player-Info"
               }
               importedplayli* {
                  pBr $player
                  GetImportedPlaylists;
                  listPlaylist playlist "Imported Playlists &nbsp; ($playlistcount Einträge)"
                  listArray  sonosArray  "Player-Info"
               }
               
               mut* {
                  pBr $player
                  List2Values "Mutestate:" [GetMute]
                  listArray  sonosArray  "Player-Info"
               }
               crossfade* {
                  pBr $player
                  List2Values "Crossfademode:"[GetCrossfadeMode]
                  listArray  sonosArray  "Player-Info"
               }
               repea* {
                  GetTransportSettings
                  pBr $player
                  List2Values "Repeat:" [sonosGet repeat]
                  listArray  sonosArray  "Player-Info" "1"
               }
               shuf* {
                  GetTransportSettings
                  pBr $player
                  List2Values "Shuffle:" [sonosGet shuffle]
                  listArray  sonosArray  "Player-Info"
               }
               vol* {
                  pBr $player
                  List2Values "Volume:" [GetVolume]
                  listArray  sonosArray  "Player-Info"
              }
               led* {
                  pBr $player
                  List2Values "LEDState:" [GetLEDState]
                  listArray  sonosArray  "Player-Info"
               }
               askradio* {
                   pBr $player
                   AskRadiotime
                   if { [info exists info(Image)]} {
                     set image $info(Image)
                     if { $br == "<br>" } {
                        puts "<img src='$image' alt='$image' class='img-rounded'>"
                        unset info(Image)
                     }
                   }
                   listArray info "Radio-Info"
                   listArray  sonosArray  "Player-Info"
              }
               default {
                  pBr
                  listArray  sonosArray  "Player-Info"
                  pBr "Info $bold'$args(info)'$unbold nicht gefunden!"
               }
            }
         } else {
             pBr $player
            listArray  sonosArray  "Player-Info"
         }
      }
      default {
         pBr $player
         listArray  sonosArray  "Player-Info"
         pBr "${bold}Action '$action' not recognized!$unbold"
      }
   }                
} {
   pBr $player
   listArray  sonosArray  "Player-Info"
   pBr "${bold}No action is given!$unbold"
}
theEnd