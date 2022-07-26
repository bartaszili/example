#!/bin/sh

m_c=start*.html
m_p=$(pwd)/data/tmp/bazos
# m_p=$(pwd)/data/tmp/nehnutelnosti
# m_p=$(pwd)/data/tmp/reality
# m_p=$(pwd)/data/tmp/topreality
m_t=bazos.sk
# m_t=nehnutelnosti.sk
# m_t=reality.sk
# m_t=topreality.sk
m_d=*db_dump.json
m_l=*list-file.json
m_x=*html.lock

if [ -d $m_p ]
  then
    inotifywait -qm --format %w $m_p |
    while read file
    do
      clear
      echo "••• $m_t"
      echo "╔═══════════════════"
      echo "║ crawled:  $(find $m_p -type f -name $m_c | wc -l)"
      echo "╠═══════════════════"
      echo "║ locked:   $(find $m_p -type f -name $m_x | wc -l)"
      echo "╠═══════════════════"
      echo "║ scanners: $(find $m_p -type f -name $m_d | wc -l)"
      echo "╠═══════════════════"
      echo "║ lists:    $(find $m_p -type f -name $m_l | wc -l)"
      echo "╚═══════════════════"
      sleep 2.5s
    done
fi

exit 0;
