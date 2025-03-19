#!/bin/bash

# Skript-Aufgabe:
# Nimmt die Log-Datei des Peak-Mem-Loggers entgegen und sortiert die Einträge absteigend 


path=$1


# how to call this script

if [[ ! ( "${path:0:2}" == "./" || "${path:0:1}" == "/" ) ]]; then
	echo ""
	echo "Falscher Aufruf: Dateiargument muss eine relative oder absolute Pfadangabe haben."
	echo ""
	exit 1
fi


# check if the argument is a relative path

if [[ "${path:0:2}" == "./" ]]; then
	path="${path#.}"  #remove "." as prefix
	path="$(pwd)$path"
fi


# check if the input arg is really a file:

if [[ ! -f "$path" ]]; then
	echo ""
	echo "$1 ist keine gültige Datei"
	exit 1
fi


# do the actual evaluation

sed 's/\[mem peak usage\]/[mem_peak_usage]/' < "$path" | sort -g -k3


