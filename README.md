Tónlistar-leitareining (music-search)

Installation process
First install Docker, ddev and composer.
Clone the repository and set your current working directory to the music_search directory.
run this command to install all its dependencies
```
  $ composer install
```
In order to run the website, you must configure the database using this command
```
  $ ddev import-db --src=database/database.sql.gz
```

To run the program use this command
```
  $ ddev start
```
Open the site at http://verkefni-2.ddev.site and start using our application.
If you do not see any media, you have to replace web/sites/default/files folder
with this files folder that you can download from this google drive link
https://drive.google.com/drive/folders/1tpuXFDICB6-2ryfuFpwccER4fd9F6xaC?usp=sharing

How to use the music search module
Click on the extend in the admin panel and enable the music search module in the custom section.
