# SOURCES

### Location data by counties
---
Sourced from [data.gov.sk -> by counties](https://data.gov.sk/dataset/adresy-podla-krajov). Needs to be ***filtered*** for invalid `postcode` or invalid `geo coordinates`. There are ***swapped*** geo coordinates also. Then overall ***sorting*** and filter out ***unique*** records.  

As an alternative, when the data are corrupt from the example above (missing large amount of locations), visit [all counties](https://data.gov.sk/en/dataset/adresy-podla-krajov/resource/5322f6c6-1b09-47fc-bb47-83dd35b4e404) page, grab the `*.rar` file and manually separate to individual county files.  

In DatasetsController edit step=1 substep=1 section and step=1 substep=11 section. Just swap the if condition.



### Separate location data
---
Sourced from:
* [data.gov.sk -> county](https://data.gov.sk/dataset/register-adries-register-krajov)
* [data.gov.sk -> district](https://data.gov.sk/dataset/register-adries-register-okresov)
* [data.gov.sk -> town](https://data.gov.sk/dataset/register-adries-register-obci)
* [data.gov.sk -> town_part](https://data.gov.sk/dataset/register-adries-register-casti-obci)
* [data.gov.sk -> street](https://data.gov.sk/dataset/register-adries-register-ulic)

Combine them together.

### Cross Reference Tables
---
Sourced from [zakonipreludi.sk](https://www.zakonypreludi.sk/zz/2002-597#prilohy)\
Manualy copy the attachements data.\
***Grammer errors present, change `Iľava` to `Ilava`***

* xref_counties.csv
* xref_districts.csv

### Poscodes
---
Soruced from [posta.sk](https://www.posta.sk/sluzby/postove-smerovacie-cisla)\
***Many errors!***

* `OBCE.xlsx` -> `postcode_post_town.csv`
* `ULICE.xlsx` -> `postcode_post_street.csv`

1. `ULICE.xlsx`\
In column `POZNAMKA` find `verejné` and delete row.\
Fill in missing postcodes in `ULICE.xlsx` using `VLOOKUP` function.\
`=VLOOKUP(RIGHT(TRIM(C812);LEN(TRIM(C812))-6);A:B;2;FALSE())`\
Whatever remains needs to be done manually.\
Use columns `DULICA, PSC, OBCE` in `ULICE.xlsx`, sort them.

2. `OBCE.xlsx`\
Delete rows with empty `PSC`.\
Use columns `DOBEC, PSC, KRAJ, KOD_OKR`.\
Find and delete `\s\(.+\)` in `DOBEC` column.\
Find and delete `,\s\w.+` in `DOBEC` column.\
Spilt `DOBEC` by `-` to `DOBEC,CAST`.\
Find and delete `časť ` in `CAST` column.\
Sort and filter out unique rows.\
Combine sheets in `OBCE.xlsx`.\
Sort and make it unique again.