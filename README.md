Foursquare-Force-Directed_Graph
====================

D3.js Force-Directed Graph of Foursquare Check-ins

This code has been customized to better illustrate personal check-ins, therefore, the CSS and other elements such as time span would need to be updated. 

4sq.php
 - Update $sd and $ed to reflect the start and end dates that wish to be displayed.
 - JS variables lengend and legendCC would need to be updated with the CSS styles of the .nodes* and .lengendCountry

4sq-json.php
 - Update the $FSQ_CLIENT_ID, $FSQ_CLIENT_SECRET, $FSQ_VERSION to reflect the Foursquare API credentials
 - Function determineHighLevelCategory can be updated to reflect the categories in your Foursquare file
 - Function createForceGraphJson loads the 4sq.kml file, which contains Foursquare checkins. Download from https://foursquare.com/feeds/
 - This script will take the KML file and create the corresponding .json file that the 4sq.php file will use to create the graph.
 - This script will take your KML file and download corresponding venue data from Foursquare. This will cache the downloaded json file into the current working directory. When the page is reloaded, the cached version will be used instead of pulling data from Foursquare. The initial run will take time as each venue file will be downloaded. Consecutive runs are considerably faster as the data is already cached.