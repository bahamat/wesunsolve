#!/bin/bash

repos=/srv/sunsolve/repository/patches
pushd ${PWD} > /dev/null 2>&1
cd ${repos};

echo -n "[-] Cleaning null sized files..."
find . -size 0|xargs rm -f
echo "done";

echo -n "[-] Cleaning empty directories..."
find . -depth -type d -empty
echo "done";

nb=0;

for dir1 in *; do
  if [ "$dir1" = "*" ]; then
     continue;
  fi
  cd ${dir1};
  echo "[-] Entering ${dir1}";
  for dir2 in *; do
    if [ "$dir2" = "*" ]; then
      continue;
    fi
    cd ${dir2};
    echo "[-] Entering ${dir2}";
    for p in *.tar.Z; do
      if [ "${p}" = "*.tar.Z" ]; then
        continue;
      fi
      echo "[-] Treating ${p}...";
      patch=$(echo ${p} | cut -f 1 -d'.');
      if [ ! -f README.${patch} ]; then
        echo "[-] Extracting README for ${patch}";
        gzip -dc ./${p}| tar --no-recursion -C . -xf - ${patch}/README.${patch} > /dev/null 2>&1
        gzip -dc ./${p}| tar --no-recursion -C . -xf - ${patch}/README > /dev/null 2>&1
        if [ ! -f ${patch}/README.${patch} -a -f ${patch}/README ]; then
          mv ${patch}/README ${patch}/README.${patch}
        fi
        mv ${patch}/README.${patch} .
	rm -rf ${patch};
      fi;
      if [ ! -f ${p}.md5sum ]; then
        echo "[-] MD5 Checksumming of ${patch}";
        nb=`expr $nb + 1`
        md5sum ${p} > ${p}.md5sum
      fi
      if [ ! -f ${p}.sha512sum ]; then
        echo "[-] SHA256 Checksumming of ${patch}";
        sha512sum ${p} > ${p}.sha512sum
      fi
    done;
    for p in *.zip; do
      if [ "${p}" = "*.zip" ]; then
        continue;
      fi
      echo "[-] Treating ${p}...";
      patch=$(echo ${p} | cut -f 1 -d'.');
      if [ ! -f README.${patch} ]; then
        echo "[-] Extracting README for ${patch}";
        unzip -j ${p} ${patch}/README.${patch} > /dev/null 2>&1
      fi
      if [ ! -f ${p}.md5sum ]; then
        echo "[-] MD5 Checksumming of ${patch}";
        nb=`expr $nb + 1`
        md5sum ${p} > ${p}.md5sum
      fi
      if [ ! -f ${p}.sha512sum ]; then
        echo "[-] SHA256 Checksumming of ${patch}";
        sha512sum ${p} > ${p}.sha512sum
      fi
    done;
    cd ..
  done;
cd ..
done;
echo "[-] $nb new patch treated...";

popd > /dev/null 2>&1
