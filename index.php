<?php

define('API_URL', 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson');

function fetchEarthquakeData() {
    $apiUrl = API_URL;
    $response = file_get_contents($apiUrl);

    if ($response === FALSE) {
        throw new Exception('Error occurred while fetching data.');
    }

    $data = json_decode($response, true);

    if ($data === NULL) {
        throw new Exception('Error occurred while decoding JSON.');
    }

    return $data;
}


function displayEarthquakes($earthquakes) {
    echo "<h1>Recent Earthquakes in the Philippines</h1>";

    if (count($earthquakes) > 0) {
        echo "<ul>";
        foreach ($earthquakes as $quake) {
            $magnitude = $quake['properties']['mag'];
            $location = $quake['properties']['place'];
            $time = date('Y-m-d H:i:s', $quake['properties']['time'] / 1000);
            $coordinates = $quake['geometry']['coordinates'];

            echo "<li>
                    <strong>Magnitude:</strong> $magnitude, 
                    <strong>Location:</strong> $location, 
                    <strong>Time:</strong> $time
                    <br><small>Coordinates: Latitude {$coordinates[1]}, Longitude {$coordinates[0]}</small>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No recent earthquakes in the Philippines.</p>";
    }
}

// Main logic
try {
    $data = fetchEarthquakeData();

  
    $philippines_earthquakes = array_filter($data['features'], function($quake) {
        $place = $quake['properties']['place'];
        return strpos($place, 'Philippines') !== false;
    });

    displayEarthquakes($philippines_earthquakes);
} catch (Exception $e) {
    echo '<p>Error: ' . $e->getMessage() . '</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthquake Data</title>
    <style>
        /* Basic styles for your earthquake app */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        p {
            color: red;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <!-- Content will be inserted here by PHP -->
    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([12.8797, 121.7740], 6); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        <?php
  
        foreach ($philippines_earthquakes as $quake) {
            $magnitude = $quake['properties']['mag'];
            $latitude = $quake['geometry']['coordinates'][1];
            $longitude = $quake['geometry']['coordinates'][0];
            $location = $quake['properties']['place'];

          
            $radius = $magnitude * 10000; 

            echo "L.circle([$latitude, $longitude], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: $radius
            }).addTo(map).bindPopup('<b>Magnitude:</b> $magnitude<br><b>Location:</b> $location');";
        }
        ?>
    </script>
</body>
</html>
