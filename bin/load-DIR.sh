#!/bin/bash

if [ $# -ne 1 ]; then
  echo "$0 <Directory>";
  exit;
fi

dir=$1
repos="/srv/sunsolve/repository/patches";
if [ ! -d ${dir} ]; then
  echo "[!] ${eis} not found";
  exit;
fi

find ${dir} -name *.zip | egrep "[0-9]{6}-[0-9]{2}.zip$" | while read file; do
  patch=`echo ${file}|rev|cut -f 1 -d'/'|rev`;
  dir1=`echo ${patch} | cut -c 1-2`;
  dir2=`echo ${patch} | cut -c 3-4`;
  ddir="${repos}/${dir1}/${dir2}";
  dest="${ddir}/${patch}";
  if [ ! -d ${ddir} ]; then
    mkdir -p ${ddir};
  fi
  if [ ! -f ${dest} ]; then
    echo -n "[-] Copying ${patch} to ${ddir}...";
    cp ${file} ${dest}
    echo "done";
  else
    echo "[-] ${patch} Already present, skipping";
  fi
done;
find ${dir} -name *.tar.Z | egrep "[0-9]{6}-[0-9]{2}.tar.Z$" | while read file; do
  patch=`echo ${file}|rev|cut -f 1 -d'/'|rev`;
  dir1=`echo ${patch} | cut -c 1-2`;
  dir2=`echo ${patch} | cut -c 3-4`;
  ddir="${repos}/${dir1}/${dir2}";
  dest="${ddir}/${patch}";
  if [ ! -d ${ddir} ]; then
    mkdir -p ${ddir};
  fi 
  if [ ! -f ${dest} ]; then
    echo -n "[-] Copying ${patch} to ${ddir}...";
    cp ${file} ${dest}
    echo "done";
  else
    echo "[-] ${patch} Already present, skipping";
  fi
done;
popd > /dev/null 2>&1
