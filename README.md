# Tónlistar-leitareining (music-search)
- Eggert Már Eggertsson and Kristófer Gauti Þórhallsson.
Installation process
First install Docker, ddev and composer.
Clone the repository and set your current working directory to the music_search directory.
run this command to install all its dependencies
```
  composer install
```
In order to run the website, you must configure the database using this command
```
  ddev import-db --src=database/database.sql.gz
```

To run the program use this command
```
  ddev start
```
#Module installation
To install the music-search, you go to Extend, and there you should see
the module music-search under Custom. When the module is installed, the landing page
for the search is at /radio_buttons_form. There you can choose:
- search for artist
- search for album
- search for track

This makes it so that the queries to either the Spotify API, or the Discogs API are a lot more exact.
From there on you can type what it is that you are searching for.
When the search is made, it will lead you to the search-results page, where you can narrow down the
data, even further, that is supposed to go in the database. After you have narrowed it down,
you will get a list of the marked data, and you can choose from there what data you want from which web service (Spotify or Discogs).
After you have confirmed the data that is supposed to be inserted into the database
, the data goes through a validation function and if everything matches, the data is inserted into the Drupal database.
We did not complete the artist insertion functionality, because of time limitations.


Open the site at http://verkefni-2.ddev.site and start using our application.
If you do not see any media, you have to replace web/sites/default/files folder
with this files folder that you can download from this Google drive link
https://drive.google.com/drive/folders/1tpuXFDICB6-2ryfuFpwccER4fd9F6xaC?usp=sharing

How to use the music search module
Click on the extend in the admin panel and enable the music search module in the custom section.
