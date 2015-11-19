#******************************************************************
#** File: Makefile
#** Description: the makefile for newudp program
#******************************************************************

CROSS = /usr/bin/

CFLAGS = -O3 -Wall -pedantic -std=gnu99
CC = $(CROSS)gcc
STRIP = $(CROSS)strip --strip-unneeded

all: newudp

newudp: newudp.c
	$(CC) $(CFLAGS) -o newudp newudp.c
	$(STRIP) newudp

clean:
	rm -f newudp
