sed 's/^\?//' soubor.tex| sed 's/\\(%i[0-9]*\\)//'|sed 's/"//' | sed 's/konec;/konec/'| sed 's/\\\\/@backslash@/g'| sed 's/\\//g'| sed 's/@backslash@/\\/g' | sed 's/\\zadani y/\\zadani z/' | sed 's/\\krok {y}\^\\prime/\\krok z\^\\prime_x/g' | sed 's/\\uprava {y}\^\\prime/\\uprava z\^\\prime_x/g'